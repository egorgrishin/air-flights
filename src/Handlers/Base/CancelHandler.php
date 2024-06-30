<?php
declare(strict_types=1);

namespace App\Handlers\Base;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Enums\Method;
use App\Handlers\Handler;

final readonly class CancelHandler extends Handler
{
    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === State::CancelMonitoring->value;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $this->telegram->send(Method::Delete, [
            'chat_id'    => $this->fromId,
            'message_id' => $this->messageId,
        ]);
        $this->sendSuccessCallback();
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    protected function parseDto(DtoContract $dto): void
    {
    }
}