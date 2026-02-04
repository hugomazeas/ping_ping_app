<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PingBackup extends Command
{
    protected $signature = 'ping:backup';

    protected $description = 'Backup SQLite database to timestamped file';

    public function handle(): int
    {
        $sourcePath = database_path('database.sqlite');

        if (!File::exists($sourcePath)) {
            $this->error('Database file not found at: ' . $sourcePath);
            return Command::FAILURE;
        }

        // Create backups directory if it doesn't exist
        $backupDir = database_path('backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Create timestamped backup filename
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = $backupDir . '/database_' . $timestamp . '.sqlite';

        // Copy the database file
        File::copy($sourcePath, $backupPath);

        $this->info('Backup created successfully!');
        $this->line('Location: ' . $backupPath);

        // Show file size
        $sizeBytes = File::size($backupPath);
        $sizeKb = round($sizeBytes / 1024, 2);
        $this->line("Size: {$sizeKb} KB");

        // List existing backups
        $backups = File::glob($backupDir . '/database_*.sqlite');
        $this->line('');
        $this->line('Total backups: ' . count($backups));

        return Command::SUCCESS;
    }
}
