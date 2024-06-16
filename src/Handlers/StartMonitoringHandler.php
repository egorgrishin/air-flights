<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Core\Telegram;
use App\Repositories\AirportRepository;

final class StartMonitoringHandler implements HandlerContract
{
    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === 'Начать мониторинг';
    }

    public function process(DtoContract $dto): void
    {
        $airports = (new AirportRepository())->getAll();
        $airports = array_slice($airports, 0, 5);
        foreach ($airports as &$airport) {
            $airport = [
                [
                    'text'          => $airport->title,
                    'callback_data' => "sel_dep:$airport->code:>:0",
                ],
            ];
        }

        Telegram::send('sendMessage', [
            'chat_id'      => $dto->fromId,
            'text'         => "Выберите аэропорт отправления",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$airports,
                    [
                        ['text' => '->', 'callback_data' => 'sel_dep:>:5'],
                    ],
                ],
//                'one_time_keyboard' => true,
//                'resize_keyboard'   => true,
            ],
        ]);
    }
}