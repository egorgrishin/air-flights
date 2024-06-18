<?php
declare(strict_types=1);

namespace App;

use App\Contracts\DtoContract;
use App\Handlers\AcceptHandler;
use App\Handlers\ArrNavigationHandler;
use App\Handlers\DateDayHandler;
use App\Handlers\DateMonthHandler;
use App\Handlers\DepNavigationHandler;
use App\Handlers\StartHandler;
use Exception;

class HandlerFactory
{
    /**
     * @throws Exception
     */
    public static function make(DtoContract $dto): Handler
    {
        return match (true) {
            StartHandler::validate($dto) => new StartHandler($dto),
            DepNavigationHandler::validate($dto) => new DepNavigationHandler($dto),
            ArrNavigationHandler::validate($dto) => new ArrNavigationHandler($dto),
            DateMonthHandler::validate($dto) => new DateMonthHandler($dto),
            DateDayHandler::validate($dto) => new DateDayHandler($dto),
            AcceptHandler::validate($dto) => new AcceptHandler($dto),
            default => throw new Exception(),
        };
    }
}