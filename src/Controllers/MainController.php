<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Searchers\PobedaSearch;
use App\Searchers\SmartaviaSearch;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MainController
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
//        $log = new Logger('name');
//        $log->pushHandler(new StreamHandler('path/to/your.log', Level::Warning));
//
        $dateTime = \DateTime::createFromFormat('Y-m-d', '2024-06-15');
        (new PobedaSearch())->run($dateTime);
        (new SmartaviaSearch())->run($dateTime);

        dd($request->getParsedBody());
        $response->getBody()->write("Hello world! 77 ");
        return $response;
    }
}