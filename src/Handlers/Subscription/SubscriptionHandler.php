<?php
declare(strict_types=1);

namespace App\Handlers\Subscription;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handlers\Handler;
use App\Repositories\SubscriptionsRepository;
use App\VO\Subscription;
use DateTime;

final readonly class SubscriptionHandler extends Handler
{
    private SubscriptionsRepository $repository;
    private int $offset;
    private int $limit;
    private ?int $subsId;
    private string $selfState;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new SubscriptionsRepository();
        $this->selfState = State::SubsSelect->value;
        $this->limit = 8;
        parent::__construct($dto);
    }

    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        $state = State::SubsSelect->value;
        return $dto->data === State::SubscriptionsList->value
            || preg_match("/^$state:\d+(:\d+)?$/", $dto->data) === 1;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        if ($this->subsId) {
            $this->repository->blockSubscriptionById($this->subsId, $this->fromId);
        }

        $subs = $this->repository->getChatSubscriptions($this->fromId, $this->offset, $this->limit);
        $subsCount = $this->repository->getChatSubscriptionsCount($this->fromId);

        $this->telegram->send(
            $this->method,
            $this->getMessageData($subs, $subsCount)
        );
        $this->sendSuccessCallback();
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    protected function parseDto(DtoContract $dto): void
    {
        $data = $dto->data === State::SubscriptionsList->value ? "$this->selfState:0" : $dto->data;
        $data = explode(':', $data);
        $this->offset = (int) $data[1];
        $this->subsId = empty($data[2]) ? null : (int) $data[2];
    }

    /**
     * Возвращает данные для отправки сообщения в Telegram
     */
    private function getMessageData(array $subscriptions, int $subsCount): array
    {

        return [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => $this->getMessageText($subscriptions),
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getSubsButtons($subscriptions),
                    $this->getNavigationButtons($subsCount),
                    $this->getMenuButtons(),
                ],
            ],
        ];
    }

    /**
     * Возвращает текст сообщения со списком подписок
     *
     * @param Subscription[] $subscriptions
     * @return string
     */
    private function getMessageText(array $subscriptions): string
    {
        $text = "✅️ Активные подписки:\n\n";
        for ($i = 0; $i < count($subscriptions); $i++) {
            $num = $i + $this->offset + 1;
            $subscription = $subscriptions[$i];
            $date = DateTime::createFromFormat('Y-m-d', $subscription->date)->format('d.m.Y');
            $text .= "$num. $date, $subscription->depTitle — $subscription->arrTitle";
            $text .= ($subscription->minPrice ? ", {$subscription->minPrice}р.\n\n" : "\n\n");
        }
        $text .= "❗️ Если хочешь удалить одну из подписок, просто нажми на ее номер, и она исчезнет";

        return $text;
    }

    /**
     * Возвращает массив кнопок клавиатуры для удаления сообщений
     *
     * @param Subscription[] $subscriptions
     * @return array
     */
    private function getSubsButtons(array $subscriptions): array
    {
        $buttons = [];
        for ($i = 0; $i < count($subscriptions); $i++) {
            $num = $i + $this->offset + 1;
            $row = intdiv($i, 4);
            if (empty($buttons[$row])) {
                $buttons[$row] = [];
            }
            $buttons[$row][] = [
                'text'          => $num,
                'callback_data' => "$this->selfState:$this->offset:{$subscriptions[$i]->id}",
            ];
        }
        return $buttons;
    }

    /**
     * Возвращает кнопки для постраничной навигации
     */
    private function getNavigationButtons(int $subsCount): array
    {
        $buttons = [];
        if ($this->offset > 0) {
            $newStart = max(0, $this->offset - $this->limit);
            $buttons[] = [
                'text'          => '⬅️',
                'callback_data' => "$this->selfState:$newStart",
            ];
        }
        $end = $this->offset + $this->limit;
        if ($end < $subsCount) {
            $buttons[] = [
                'text'          => '➡️',
                'callback_data' => "$this->selfState:$end",
            ];
        }
        return $buttons;
    }

    /**
     * Возвращает массив кнопок навигации
     */
    private function getMenuButtons(): array
    {
        return [
            [
                'text'          => 'Закрыть ❌️',
                'callback_data' => State::CancelMonitoring->value,
            ],
            [
                'text'          => 'Обновить 🔄',
                'callback_data' => "$this->selfState:$this->offset",
            ],
        ];
    }
}