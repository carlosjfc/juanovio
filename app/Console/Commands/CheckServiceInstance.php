<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckServiceInstance extends Command
{
    protected $signature = 'website:check-service-instance {instance}';

    protected $description = 'Validate managed-service identity and queue configuration without processing jobs';

    public function handle(): int
    {
        $instance = (string) $this->argument('instance');
        $connection = config('queue.connections.'.config('queue.default'), []);
        $queue = (string) ($connection['queue'] ?? '');

        if (! preg_match('/\A[a-z0-9][a-z0-9-]{0,62}\z/', $instance)
            || config('queue.service_instance') !== $instance) {
            $this->error('Service identity differs from configured SERVICE_INSTANCE. Rebuild configuration for this deployment.');

            return self::FAILURE;
        }

        if (! in_array($connection['driver'] ?? null, ['database', 'redis'], true)
            || ! str_starts_with($queue, $instance.'.')
            || ! preg_match('/\A[a-zA-Z0-9_.-]+\z/', $queue)
            || strlen($queue) <= strlen($instance) + 1
            || (int) ($connection['retry_after'] ?? 0) <= 60
            || (int) ($connection['block_for'] ?? 0) !== 0) {
            $this->error('Managed workers require database/redis, a queue prefixed with instance., retry_after > 60 seconds and nonblocking polling.');

            return self::FAILURE;
        }

        $this->info('Service identity and queue configuration are valid. No jobs processed; backend connectivity and service health are not checked.');

        return self::SUCCESS;
    }
}
