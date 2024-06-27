<?php
declare(strict_types=1);

namespace App\Handlers\Base;

use App\Contracts\DtoContract;
use App\Handlers\Handler;

final readonly class NotFoundHandler extends Handler
{
    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        return true;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $this->telegram->send($this->method, [
            'chat_id'    => $this->fromId,
            'message_id' => $this->messageId,
            'text'       => "Я тебя не понимаю :(\nПопробуй еще раз",
        ]);
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    protected function parseDto(DtoContract $dto): void
    {
    }
}