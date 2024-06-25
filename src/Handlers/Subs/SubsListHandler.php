<?php
declare(strict_types=1);

namespace App\Handlers\Subs;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handlers\Handler;
use App\Repositories\SubscriptionsRepository;
use App\VO\Subscription;
use DateTime;

final readonly class SubsListHandler extends Handler
{
    private SubscriptionsRepository $repository;
    private int $start;
    private int $limit;
    private string $selfState;
    private string $nextState;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new SubscriptionsRepository();
        $this->selfState = State::SubsSelect->value;
        $this->nextState = State::SubsDelete->value;
        $this->limit = 5;
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = State::SubsSelect->value;
        return $dto->data === State::SubscriptionsList->value
            || preg_match("/^$state:[<>]:\d+$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $subs = $this->repository->getChatSubscriptions((string) $this->fromId, $this->start, $this->limit);
        $subsCount = $this->repository->getChatSubscriptionsCount((string) $this->fromId);

        $this->telegram->send(
            $this->method,
            $this->getMessageData($subs, $subsCount)
        );
    }

    protected function parseDto(DtoContract $dto): void
    {
        $data = $dto->data === State::SubscriptionsList->value ? "$this->selfState:>:0" : $dto->data;
        [, $sign, $index] = explode(':', $data);
        $this->start = (int) ($sign === '>' ? $index : ($index - 5));
    }

    private function getMessageData(array $subs, int $subsCount): array
    {
        return [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Список подписок\nДля удаления нажмите",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getSubsButtons($subs),
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
        foreach ($subs as $sub) {
            $date = DateTime::createFromFormat('Y-m-d', $sub->date)->format('d.m.Y');
            $buttons[] = [
                [
                    'text'          => "$date $sub->depCode-$sub->arrCode {$sub->minPrice}р.",
                    'callback_data' => "$this->nextState:>:$this->start:$sub->id",
                ],
            ];
        }
        return $buttons;
    }

    private function getNavigationButtons(int $subsCount): array
    {
        $buttons = [];
        if ($this->start > 0) {
            $buttons[] = [
                'text'          => '<-',
                'callback_data' => "$this->selfState:<:$this->start",
            ];
        }
        $end = $this->start + $this->limit;
        if ($end < $subsCount) {
            $buttons[] = [
                'text'          => '->',
                'callback_data' => "$this->selfState:>:$end",
            ];
        }
        return $buttons;
    }

    private function getMenuButtons(): array
    {
        return [
            [
                'text'          => 'Обновить',
                'callback_data' => "$this->selfState:>:$this->start",
            ],
        ];
    }
}