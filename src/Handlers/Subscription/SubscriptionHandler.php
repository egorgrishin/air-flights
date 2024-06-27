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
        $this->limit = 25;
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
     * @param Subscription[] $subscriptions
     * @param int $subsCount
     * @return array
     */
    private function getMessageData(array $subscriptions, int $subsCount): array
    {
        $text = "✅️ Активные подписки:\n";
        for ($i = 0; $i < count($subscriptions); $i++) {
            $num = $i + $this->offset + 1;
            $subscription = $subscriptions[$i];
            $date = DateTime::createFromFormat('Y-m-d', $subscription->date)->format('d.m.Y');
            $text .= "$num. $date,  $subscription->depTitle — $subscription->arrTitle";
            $text .= ($subscription->minPrice ? ", {$subscription->minPrice}р.\n" : "\n");
        }

        return [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => $text,
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
     * @param Subscription[] $subs
     * @return array
     */
    private function getSubsButtons(array $subs): array
    {
        $buttons = [];
        for ($i = 0; $i < count($subs); $i++) {
            $num = $i + $this->offset + 1;
            $row = intdiv($i, 5);
            if (empty($buttons[$row])) {
                $buttons[$row] = [];
            }
            $buttons[$row][] = [
                'text'          => $num,
                'callback_data' => "$this->selfState:$this->offset:{$subs[$i]->id}",
            ];
        }
        return $buttons;
    }

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

    private function getMenuButtons(): array
    {
        return [
            [
                'text'          => 'Обновить 🔄',
                'callback_data' => "$this->selfState:$this->offset",
            ],
        ];
    }
}