<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Core\Telegram;

final class DateMonthHandler implements HandlerContract
{
    public static function validate(DtoContract $dto): bool
    {
        return preg_match('/^sel_date:[A-Z]{3}:[A-Z]{3}$/', $dto->data) === 1;
    }

    public function process(DtoContract $dto): void
    {
        $airports = explode(':', $dto->data, 2)[1];
        $months = [
            'Декабрь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь',
        ];
        $month = (int) date('n') - 1;
        $year = (int) date('Y');
        $arr = [];
        for ($i = 0; $i < 6; $i++) {
            $num = $month + $i;
            $yearNum = $year + intdiv($num, 12);
            $mo = $num + 1;
            $arr[] = [
                [
                    'text'          => $months[$num % 12] . ", {$yearNum}г.",
                    'callback_data' => "sel_day:$airports:$mo:$yearNum",
                ],
            ];
        }

        Telegram::send('editMessageText', [
            'chat_id'      => $dto->fromId,
            'message_id'   => $dto->messageId,
            'text'         => "Выберите месяц",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$arr,
                ],
            ],
        ]);
    }
}