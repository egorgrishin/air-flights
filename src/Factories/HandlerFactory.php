<?php
declare(strict_types=1);

namespace App\Factories;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Handlers\Add\AcceptHandler;
use App\Handlers\Add\SelectArrHandler;
use App\Handlers\Add\SelectDayHandler;
use App\Handlers\Add\SelectMonthHandler;
use App\Handlers\Add\SelectDepHandler;
use App\Handlers\Add\SuccessHandler;
use App\Handlers\Base\CancelHandler;
use App\Handlers\Base\InstructionHandler;
use App\Handlers\Base\NotFoundHandler;
use App\Handlers\Base\StartHandler;
use App\Handlers\Subscription\SubscriptionHandler;

class HandlerFactory
{
    /**
     * Возвращает обработчик, который подходит для запроса
     */
    public static function make(DtoContract $dto): HandlerContract
    {
        return match (true) {
            StartHandler::validate($dto)        => new StartHandler($dto),
            InstructionHandler::validate($dto)  => new InstructionHandler($dto),
            SelectDepHandler::validate($dto)    => new SelectDepHandler($dto),
            SelectArrHandler::validate($dto)    => new SelectArrHandler($dto),
            SelectMonthHandler::validate($dto)  => new SelectMonthHandler($dto),
            SelectDayHandler::validate($dto)    => new SelectDayHandler($dto),
            AcceptHandler::validate($dto)       => new AcceptHandler($dto),
            SuccessHandler::validate($dto)      => new SuccessHandler($dto),
            CancelHandler::validate($dto)       => new CancelHandler($dto),
            SubscriptionHandler::validate($dto) => new SubscriptionHandler($dto),
            default                             => new NotFoundHandler($dto),
        };
    }
}