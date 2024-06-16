<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Core\Telegram;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final class SuccessHandler implements HandlerContract
{
    public static function validate(DtoContract $dto): bool
    {
        return preg_match('/^suc:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}:\d{1,2}$/', $dto->data) === 1;
    }

    public function process(DtoContract $dto): void
    {
        [, $dep, $arr, $month, $year, $day] = explode(':', $dto->data);
        $month = $month < 10 ? '0' . $month : $month;
        $day = $day < 10 ? '0' . $day : $day;
        $airports = (new AirportRepository())->getByCode([$dep, $arr]);
        /** @var Airport $depAirport */
        $depAirport = array_values(
            array_filter($airports, fn (Airport $airport) => $airport->code === $dep)
        )[0];
        /** @var Airport $arrAirport */
        $arrAirport = array_values(
            array_filter($airports, fn (Airport $airport) => $airport->code === $arr)
        )[0];

        $text = <<<TEXT
Мониторинг успешно активирован!
Город отправления $depAirport->title ($depAirport->code)
Город прибытия $arrAirport->title ($arrAirport->code)
Дата вылета $day.$month.$year
TEXT;


        Telegram::send('editMessageText', [
            'chat_id'      => $dto->fromId,
            'message_id'   => $dto->messageId,
            'text'         => $text,
        ]);
    }
}