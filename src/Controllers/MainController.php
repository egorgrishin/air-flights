<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MainController
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $response->getBody()->write("Hello world!");
        return $response;
    }
}