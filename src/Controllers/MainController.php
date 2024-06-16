<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Factory;
use App\Handlers\ArrNavigationHandler;
use App\Handlers\DateDayHandler;
use App\Handlers\DateMonthHandler;
use App\Handlers\AcceptHandler;
use App\Handlers\StartHandler;
use App\Handlers\StartMonitoringHandler;
use App\Handlers\DepNavigationHandler;
use App\Handlers\SuccessHandler;
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
        Container::logger()->debug(json_encode($request->getParsedBody(), JSON_PRETTY_PRINT));
        $dto = Factory::make($request);

        if (StartHandler::validate($dto)) {
            (new StartHandler())->process($dto);
        } elseif (StartMonitoringHandler::validate($dto)) {
            (new StartMonitoringHandler())->process($dto);
        } elseif (DepNavigationHandler::validate($dto)) {
            (new DepNavigationHandler())->process($dto);
        } elseif (ArrNavigationHandler::validate($dto)) {
            (new ArrNavigationHandler())->process($dto);
        } elseif (DateMonthHandler::validate($dto)) {
            (new DateMonthHandler())->process($dto);
        } elseif (DateDayHandler::validate($dto)) {
            (new DateDayHandler())->process($dto);
        } elseif (AcceptHandler::validate($dto)) {
            (new AcceptHandler())->process($dto);
        } elseif (SuccessHandler::validate($dto)) {
            (new SuccessHandler())->process($dto);
        } else {
            Container::logger()->error('NOOOOOOOO');
        }

        $response->getBody()->write("Hello world!");
        return $response;
    }

}