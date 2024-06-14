<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MainController
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        Container::logger()->debug(json_encode([
            $request->getParsedBody(), $request->getQueryParams()
        ], JSON_PRETTY_PRINT));
        $response->getBody()->write("Hello world!");
        return $response;
    }
}