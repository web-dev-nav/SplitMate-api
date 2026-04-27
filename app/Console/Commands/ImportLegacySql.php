<?php

namespace App\Console\Commands;

use Database\Seeders\LegacySqlImportSeeder;
use Illuminate\Console\Command;

class ImportLegacySql extends Command
{
    protected $signature = 'legacy:import-sql {--fresh : Run migrate:fresh before importing legacy SQL}';

    protected $description = 'Import legacy old_splitmate.sql into current schema';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->warn('Running migrate:fresh before legacy import...');
            $freshExitCode = $this->call('migrate:fresh', ['--force' => true]);

            if ($freshExitCode !== self::SUCCESS) {
                $this->error('migrate:fresh failed. Import aborted.');

                return $freshExitCode;
            }
        }

        $this->info('Running LegacySqlImportSeeder...');
        $seedExitCode = $this->call('db:seed', [
            '--class' => LegacySqlImportSeeder::class,
            '--force' => true,
        ]);

        if ($seedExitCode !== self::SUCCESS) {
            $this->error('Legacy SQL import failed.');

            return $seedExitCode;
        }

        $this->info('Legacy SQL import completed.');

        return self::SUCCESS;
    }
}
