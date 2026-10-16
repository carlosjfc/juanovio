<?php

namespace Tests\Feature;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagedServicesTest extends TestCase
{
    use RefreshDatabase;

    private function configureServices(): void
    {
        config([
            'queue.service_instance' => 'juanovio-production',
            'queue.default' => 'database',
            'queue.connections.database.queue' => 'juanovio-production.default',
            'queue.connections.database.retry_after' => 90,
        ]);
    }

    public function test_startup_rejects_wrong_identity_sync_shared_queues_and_unsafe_retries(): void
    {
        $this->configureServices();
        $this->artisan('website:check-service-instance juanovio-production')->assertSuccessful();
        $this->artisan('website:check-service-instance other-production')->assertFailed();
        foreach ([
            ['queue.default' => 'sync'],
            ['queue.connections.database.queue' => 'default'],
            ['queue.connections.database.retry_after' => 60],
            ['queue.connections.database.block_for' => 5],
        ] as $invalid) {
            $this->configureServices();
            config(['queue.connections.database.block_for' => 0]);
            config($invalid);
            $this->artisan('website:work-queue juanovio-production --once')->assertFailed();
        }
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_worker_processes_only_its_own_deployment_queue(): void
    {
        $this->configureServices();
        Queue::connection('database')->push(new ManagedServiceProbe, '', 'other-production.default');
        Queue::connection('database')->push(new ManagedServiceProbe, '', 'juanovio-production.default');
        $this->artisan('website:work-queue juanovio-production --once')->assertSuccessful();
        $this->assertTrue(Cache::get('managed-service-probe'));
        $this->assertSame(['other-production.default'], DB::table('jobs')->pluck('queue')->all());
    }

    public function test_worker_honors_custom_namespaced_queue_and_rejects_unsafe_names(): void
    {
        $this->configureServices();
        config(['queue.connections.database.queue' => 'juanovio-production.inquiries']);
        Queue::connection('database')->push(new ManagedServiceProbe);
        Queue::connection('database')->push(new ManagedServiceProbe, '', 'juanovio-production.default');
        $this->artisan('website:work-queue juanovio-production --once')->assertSuccessful();
        $this->assertTrue(Cache::get('managed-service-probe'));
        $this->assertSame(['juanovio-production.default'], DB::table('jobs')->pluck('queue')->all());

        foreach (['other-production.inquiries', 'juanovio-production.', 'juanovio-production.inquiries,other', 'juanovio-production.inquiries other'] as $queue) {
            config(['queue.connections.database.queue' => $queue]);
            $this->artisan('website:check-service-instance juanovio-production')->assertFailed();
        }
    }

    public function test_configuration_defaults_isolate_queues_cache_and_redis_and_preserve_overrides(): void
    {
        $environment = [
            'SERVICE_INSTANCE' => 'juanovio-testing', 'APP_NAME' => 'Juanovio',
            'DB_QUEUE' => false, 'REDIS_QUEUE' => false, 'SQS_QUEUE' => false,
            'BEANSTALKD_QUEUE' => false, 'CACHE_PREFIX' => false, 'REDIS_PREFIX' => false,
        ];
        $code = 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $q = require "config/queue.php"; $c = require "config/cache.php"; $d = require "config/database.php"; echo json_encode([$q, $c["prefix"], $d["redis"]["options"]["prefix"]]);';
        $read = function (array $overrides = []) use ($environment, $code): array {
            $result = Process::env(array_replace($environment, $overrides))->run([PHP_BINARY, '-d', 'display_errors=stderr', '-r', $code]);
            $this->assertTrue($result->successful(), $result->errorOutput());

            return json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR);
        };

        [$queue, $cache, $redis] = $read();
        foreach (['database', 'redis', 'sqs', 'beanstalkd'] as $connection) {
            $this->assertSame('juanovio-testing.default', $queue['connections'][$connection]['queue']);
        }
        $this->assertSame('juanovio-testing-cache-', $cache);
        $this->assertSame('juanovio-testing-database-', $redis);

        [$queue, $cache, $redis] = $read(['DB_QUEUE' => 'juanovio-testing.inquiries', 'CACHE_PREFIX' => 'custom-cache-', 'REDIS_PREFIX' => 'custom-redis-']);
        $this->assertSame('juanovio-testing.inquiries', $queue['connections']['database']['queue']);
        $this->assertSame('custom-cache-', $cache);
        $this->assertSame('custom-redis-', $redis);

        foreach (['', false] as $missingInstance) {
            [$queue, $cache, $redis] = $read(['SERVICE_INSTANCE' => $missingInstance]);
            $this->assertSame('default', $queue['connections']['database']['queue']);
            $this->assertSame('juanovio-cache-', $cache);
            $this->assertSame('juanovio-database-', $redis);
        }
    }

    public function test_rendering_uses_juanovio_units_and_safe_supervision(): void
    {
        $result = Process::run(['bash', base_path('bin/manage-services'), '--render', '--instance', 'juanovio-production', '--user', 'juanovio', '--path', '/var/www/juanovio/current', '--php', '/usr/bin/php']);
        $this->assertTrue($result->successful(), $result->errorOutput());
        foreach (['worker-default', 'scheduler'] as $role) {
            $this->assertStringContainsString('juanovio-juanovio-production-'.$role.'.service', $result->output());
        }
        $this->assertSame(2, substr_count($result->output(), 'website:check-service-instance juanovio-production'));
        $this->assertSame(2, substr_count($result->output(), 'Restart=always'));
        $this->assertStringContainsString('artisan website:work-queue juanovio-production', $result->output());
        $this->assertStringContainsString('artisan schedule:work', $result->output());
        $this->assertStringContainsString('TimeoutStopSec=180', $result->output());
        $this->assertStringNotContainsString('@PATH@', $result->output());
        $this->assertStringNotContainsString('material-freight', $result->output());
        $this->assertTrue(Process::run(['bash', '-n', base_path('bin/manage-services')])->successful());
    }

    public function test_dotenv_is_read_as_data_and_conflicting_identity_is_rejected(): void
    {
        Storage::fake('local');
        $directory = Storage::disk('local')->path('deployment');
        mkdir($directory);
        file_put_contents($directory.'/.env', "SERVICE_INSTANCE='juanovio-production'\nAPP_NAME=\$(exit 99)\n");
        $arguments = ['bash', base_path('bin/manage-services'), '--render', '--path', $directory, '--user', 'juanovio', '--php', '/usr/bin/php'];
        $result = Process::run($arguments);
        $this->assertTrue($result->successful(), $result->errorOutput());
        $this->assertStringContainsString('juanovio-juanovio-production-scheduler.service', $result->output());
        $this->assertFalse(Process::run([...$arguments, '--instance', 'other-production'])->successful());
    }

    public function test_unsafe_inputs_fail_before_host_actions(): void
    {
        foreach ([
            ['--start', '--instance', '../other'],
            ['--unknown'],
            ['--start', '--instance'],
            ['--render', '--instance', 'juanovio-production', '--user', 'root', '--php', '/usr/bin/php'],
            ['--render', '--instance', 'juanovio-production', '--user', 'juanovio', '--path', '/var/www/%n', '--php', '/usr/bin/php'],
            ['--render', '--instance', 'juanovio-production', '--user', 'juanovio', '--path', '/var/www/../other', '--php', '/usr/bin/php'],
        ] as $arguments) {
            $this->assertFalse(Process::run(['bash', base_path('bin/manage-services'), ...$arguments])->successful());
        }
    }

    public function test_lifecycle_targets_only_this_instance_and_stops_scheduler_first(): void
    {
        Storage::fake('local');
        $directory = Storage::disk('local')->path('fake-bin');
        mkdir($directory);
        file_put_contents($directory.'/systemctl', "#!/usr/bin/env bash\nprintf '%s\\n' \"\$*\"\nexit \"\${SERVICE_TEST_EXIT:-0}\"\n");
        file_put_contents($directory.'/sudo', "#!/usr/bin/env bash\n[[ \"\${1:-}\" != '--' ]] || shift\nexec \"\$@\"\n");
        chmod($directory.'/systemctl', 0755);
        chmod($directory.'/sudo', 0755);
        $environment = ['PATH' => $directory.':'.getenv('PATH')];
        foreach (['enable', 'start', 'restart', 'stop', 'status', 'check'] as $action) {
            $result = Process::env($environment)->run(['bash', base_path('bin/manage-services'), '--'.$action, '--instance', 'juanovio-production']);
            $this->assertTrue($result->successful(), $result->errorOutput());
            $this->assertStringContainsString('juanovio-juanovio-production-worker-default.service', $result->output());
            $this->assertStringContainsString('juanovio-juanovio-production-scheduler.service', $result->output());
            $this->assertStringNotContainsString('material-freight', $result->output());
            if ($action === 'stop') {
                $this->assertStringStartsWith('stop juanovio-juanovio-production-scheduler.service', $result->output());
            }
        }
        $this->assertFalse(Process::env([...$environment, 'SERVICE_TEST_EXIT' => '3'])->run(['bash', base_path('bin/manage-services'), '--check', '--instance', 'juanovio-production'])->successful());
    }
}

class ManagedServiceProbe implements ShouldQueue
{
    public function handle(): void
    {
        Cache::put('managed-service-probe', true);
    }
}
