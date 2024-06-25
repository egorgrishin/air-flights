<?php
declare(strict_types=1);

namespace App\Controllers;

use App\DtoFactory;
use App\HandlerFactory;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MainController
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $dto = DtoFactory::make($request);
        $handler = HandlerFactory::make($dto);
        $handler->process();

        $response->getBody()->write("Hello world!");
        return $response;
    }

}