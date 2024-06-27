<?php
declare(strict_types=1);

use App\Controllers\MainController;
use App\Core\Container;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true, Container::logger());
$app->post('/bot', MainController::class);
$app->get('/bot', MainController::class);
$app->run();
