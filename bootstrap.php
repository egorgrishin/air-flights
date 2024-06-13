<?php
declare(strict_types=1);

use App\Core\Env;
use App\PobedaSearch;
use App\SmartaviaSearch;

require_once __DIR__ . '/vendor/autoload.php';

Env::load();

(new PobedaSearch())->run();
(new SmartaviaSearch())->run();
