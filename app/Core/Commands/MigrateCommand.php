<?php
declare(strict_types=1);

namespace App\Core\Commands;

use App\Core\DB;

class MigrateCommand
{
    public function run(): void
    {
        $pdo = DB::getInstance();
        $paths = glob(__DIR__ . '/../../../database/migrations/*.sql');
        foreach ($paths as $path) {
            $sql = file_get_contents($path);
            $pdo->exec($sql);
        }
    }
}