<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Core\Telegram;

final class DateDayHandler implements HandlerContract
{
    public static function validate(DtoContract $dto): bool
    {
        return preg_match('/^sel_day:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}$/', $dto->data) === 1;
    }

    public function process(DtoContract $dto): void
    {
        $data = explode(':', $dto->data, 2)[1];
        [, , $month, $year] = explode(':', $data);
        $daysCount = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
        $buttons = [];
        for ($i = 0; $i < $daysCount; $i++) {
            $ind = intdiv($i, 5);
            if (empty($buttons[$ind])) {
                $buttons[$ind] = [];
            }
            $day = $i + 1;
            $buttons[$ind][] = [
                'text' => $day,
                'callback_data' => "sel_acc:$data:$day",
            ];
        }

        Telegram::send('editMessageText', [
            'chat_id'      => $dto->fromId,
            'message_id'   => $dto->messageId,
            'text'         => "Выберите день",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$buttons,
                ],
            ],
        ]);
    }
}