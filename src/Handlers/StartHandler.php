<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Handler;

final readonly class StartHandler extends Handler
{
    public function __construct(DtoContract $dto)
    {
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === '/start';
    }

    public function process(): void
    {
        $this->telegram->send($this->method, [
            'chat_id' => $this->fromId,
            'text'    => $this->getText(),
        ]);
    }

    private function getText(): string
    {
        return <<<TEXT
Привет!
Я - бот Air Flights и я занимаюсь мониторингом цен на авиабилеты!
Выберите команду из меню
TEXT;
    }

    protected function parseDto(DtoContract $dto): void
    {
    }
}