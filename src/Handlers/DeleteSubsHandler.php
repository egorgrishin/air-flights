<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handler;
use App\Repositories\SubscriptionsRepository;
use App\VO\Subscription;
use DateTime;

final readonly class DeleteSubsHandler extends Handler
{
    private SubscriptionsRepository $repository;
    private int $subsId;
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
        $state = State::SubsDelete->value;
        return preg_match("/^$state:[<>]:\d+:\d+$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $this->repository->blockSubscriptionById($this->subsId, (string) $this->fromId);
        $subs = $this->repository->getChatSubscriptions((string) $this->fromId, $this->start, $this->limit);
        $subsCount = $this->repository->getChatSubscriptionsCount((string) $this->fromId);

        $this->telegram->send(
            $this->method,
            $this->getMessageData($subs, $subsCount)
        );
    }

    protected function parseDto(DtoContract $dto): void
    {
        [, , $start, $subsId] = explode(':', $dto->data);
        $this->start = (int) $start;
        $this->subsId = (int) $subsId;
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
                    'callback_data' => "$this->nextState:$sub->id",
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
}