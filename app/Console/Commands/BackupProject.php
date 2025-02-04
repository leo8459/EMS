<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class BackupProject extends Command
{
    /**
     * El nombre y firma del comando.
     *
     * @var string
     */
    protected $signature = 'backup:project';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Genera un backup del proyecto Laravel y de la base de datos PostgreSQL en la carpeta storage/app/backups';

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle()
    {
        // 1. Crear la carpeta donde se guardarán los backups
        $backupPath = storage_path('app/backups');
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // 2. Generar un timestamp para nombres únicos
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');

        /*
         * A) Backup del proyecto
         *
         * En lugar de rar, usaremos zip porque rar no está disponible en Ubuntu.
         */
        $zipFile = $backupPath . "/project_backup_{$timestamp}.zip";
        $projectPath = base_path();
        // El comando zip comprime de forma recursiva (-r) y excluye los archivos zip anteriores
        $zipCommand = "cd \"{$projectPath}\" && zip -r \"{$zipFile}\" . -x \"storage/app/backups/*.zip\"";
        exec($zipCommand);
        $this->info("Backup del proyecto generado: {$zipFile}");

        /*
         * B) Backup de la base de datos PostgreSQL
         */
        $dbBackupFile = $backupPath . "/db_backup_{$timestamp}.sql";
        $dbHost = config('database.connections.pgsql.host');
        $dbPort = config('database.connections.pgsql.port');
        $dbName = config('database.connections.pgsql.database');
        $dbUser = config('database.connections.pgsql.username');
        $dbPass = config('database.connections.pgsql.password');

        // Construir el comando para pg_dump
        // Se utiliza PGPASSWORD para evitar que se pida la contraseña interactivamente.
        $pgDumpCommand = "PGPASSWORD=" . escapeshellarg($dbPass) .
            " pg_dump --host=" . escapeshellarg($dbHost) .
            " --port=" . escapeshellarg($dbPort) .
            " --username=" . escapeshellarg($dbUser) .
            " --dbname=" . escapeshellarg($dbName) .
            " > " . escapeshellarg($dbBackupFile);

        // (Opcional) Mostrar el comando para depuración
        $this->info("Comando pg_dump: {$pgDumpCommand}");

        // Ejecutar el comando de backup de la base de datos
        exec($pgDumpCommand, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error("Error al generar el backup de la base de datos.");
            $this->error("Salida del comando:");
            $this->error(implode("\n", $output));
        } else {
            $this->info("Backup de la base de datos generado con éxito: {$dbBackupFile}");
        }

        return Command::SUCCESS;
    }
}
