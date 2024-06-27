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

    public function __construct(DtoContract $dto)
    {
        $this->telegram = new Telegram();
        $this->fromId = $dto->fromId;
        $this->parseDto($dto);

        if ($dto instanceof CallbackDto) {
            $this->sendCallbackAnswer($dto->callbackQueryId);
            $this->method = TelegramMethod::Edit;
            $this->messageId = $dto->messageId;
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
    private function sendCallbackAnswer(string $callbackQueryId): void
    {
        try {
            $this->telegram->send(TelegramMethod::SendAnswer, [
                'callback_query_id' => $callbackQueryId,
                'text'              => "BBBBBBBBBBBBBBBBBBBBBBBB",
                'show_alert'        => true,
            ]);
            $this->telegram->send(TelegramMethod::SendAnswer, [
                'callback_query_id' => $callbackQueryId,
                'text'              => "AAAAAAAAAAAAAAAAAAAAAAAA",
                'show_alert'        => true,
            ]);
        } catch (Throwable $exception) {
            Container::logger()->error($exception);
        }
    }
}