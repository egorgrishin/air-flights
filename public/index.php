<?php
declare(strict_types=1);

use App\Controllers\MainController;
use App\Core\Container;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

Container::init();
$app = AppFactory::create();

$errorMiddleware = $app->addErrorMiddleware(true, true, true, Container::logger());

$app->get('/bot', MainController::class);
$app->run();
