<?php
declare(strict_types=1);

use App\Core\Commands\MigrateCommand;
use App\Core\Commands\SeedCommand;
use App\Core\Env;

require_once __DIR__ . '/vendor/autoload.php';

Env::load();

[, $command] = $argv;

switch ($command) {
    case 'migrate':
        (new MigrateCommand())->run();
        break;
    case 'seed':
        (new SeedCommand())->run();
        break;
}