<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WorkQueue extends Command
{
    protected $signature = 'website:work-queue {instance} {--once : Process at most one job}';

    protected $description = 'Process this deployment queue with validated managed-worker settings';

    public function handle(): int
    {
        if ($this->call('website:check-service-instance', ['instance' => $this->argument('instance')]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        return $this->call('queue:work', [
            'connection' => config('queue.default'),
            '--queue' => config('queue.connections.'.config('queue.default').'.queue'),
            '--sleep' => 3,
            '--tries' => 3,
            '--backoff' => 10,
            '--timeout' => 60,
            '--max-time' => 3600,
            '--once' => (bool) $this->option('once'),
        ]);
    }
}
