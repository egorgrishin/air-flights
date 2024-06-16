<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Core\Telegram;
use App\Repositories\CompanyRepository;

final class StartHandler implements HandlerContract
{
    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === '/start';
    }

    public function process(DtoContract $dto): void
    {
        $companies = (new CompanyRepository())->getAll();
        $text = "
Привет!
Я - бот Air Flights и я занимаюсь мониторингом цен на авиабилеты следующих компаний:
";
        foreach ($companies as $company) {
            $text .= "• " . $company->title . "\n";
        }

        Telegram::send('sendMessage', [
            'chat_id'      => $dto->fromId,
            'text'         => $text,
            'reply_markup' => [
                'keyboard'          => [
                    [
                        ['text' => 'Начать мониторинг'],
                    ],
                ],
                'one_time_keyboard' => true,
                'resize_keyboard'   => true,
            ],
        ]);
    }
}