<?php
declare(strict_types=1);

namespace App;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Handlers\Add\AcceptHandler;
use App\Handlers\Add\ArrNavigationHandler;
use App\Handlers\Add\DateDayHandler;
use App\Handlers\Add\DateMonthHandler;
use App\Handlers\Add\DepNavigationHandler;
use App\Handlers\Add\SuccessHandler;
use App\Handlers\Base\CancelHandler;
use App\Handlers\Base\NotFoundHandler;
use App\Handlers\Base\StartHandler;
use App\Handlers\Subs\DeleteSubsHandler;
use App\Handlers\Subs\SubsListHandler;

class HandlerFactory
{
    public static function make(DtoContract $dto): HandlerContract
    {
        return match (true) {
            StartHandler::validate($dto)         => new StartHandler($dto),
            DepNavigationHandler::validate($dto) => new DepNavigationHandler($dto),
            ArrNavigationHandler::validate($dto) => new ArrNavigationHandler($dto),
            DateMonthHandler::validate($dto)     => new DateMonthHandler($dto),
            DateDayHandler::validate($dto)       => new DateDayHandler($dto),
            AcceptHandler::validate($dto)        => new AcceptHandler($dto),
            SuccessHandler::validate($dto)       => new SuccessHandler($dto),
            CancelHandler::validate($dto)        => new CancelHandler($dto),
            SubsListHandler::validate($dto)      => new SubsListHandler($dto),
            DeleteSubsHandler::validate($dto)    => new DeleteSubsHandler($dto),
            default                              => new NotFoundHandler($dto),
        };
    }
}