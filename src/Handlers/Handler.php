<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Core\Container;
use App\Core\Telegram;
use App\Dto\CallbackDto;
use App\Enums\TelegramMethod;
use Throwable;

abstract readonly class Handler implements HandlerContract
{
    protected Telegram       $telegram;
    protected TelegramMethod $method;
    protected string         $fromId;
    protected ?int           $messageId;
    protected ?string        $callbackQueryId;

    public function __construct(DtoContract $dto)
    {
        $this->telegram = new Telegram();
        $this->fromId = $dto->fromId;
        $this->parseDto($dto);

        if ($dto instanceof CallbackDto) {
            $this->method = TelegramMethod::Edit;
            $this->messageId = $dto->messageId;
            $this->callbackQueryId = $dto->callbackQueryId;
        } else {
            $this->method = TelegramMethod::Send;
            $this->messageId = null;
        }
    }

    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    abstract public static function validate(DtoContract $dto): bool;

    /**
     * Обработка запроса
     */
    abstract public function process(): void;

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    abstract protected function parseDto(DtoContract $dto): void;

    /**
     * Отправляет уведомление в Telegram об успешном получении запроса
     */
    protected function sendSuccessCallback(): void
    {
        $this->sendCallbackAnswer([]);
    }

    /**
     * Отправляет уведомление в Telegram об обработке запроса
     */
    protected function sendCallbackAnswer(array $data): void
    {
        if (empty($this->callbackQueryId)) {
            return;
        }

        try {
            $this->telegram->send(
                TelegramMethod::SendAnswer,
                ['callback_query_id' => $this->callbackQueryId] + $data,
            );
        } catch (Throwable $exception) {
            Container::logger()->error($exception);
        }
    }
}