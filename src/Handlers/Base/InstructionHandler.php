<?php
declare(strict_types=1);

namespace App\Handlers\Base;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handlers\Handler;

final readonly class InstructionHandler extends Handler
{
    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === State::Instruction->value;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $text = <<<TEXT
        ● Если ты хочешь начать поиск — нажми кнопку «Start 🚀»
        ● Если ты хочешь удалить подписку - нажми в меню "Активные подписки", затем выбери маршрут, который хочешь удалить, и нажми на него. Готово!

        ❗ Почему иногда нужно поменять дату в маршруте или нет цены на билет? 
        - Нет маршрута на выбранную дату или между указанными городами
        - На данный маршрут нет билетов
        - Не отвечает авиакомпания

        ⁉️ Как работает бот Air Flights ✈️?
        - Бот находит самые выгодные билеты с официальных сайтов двух авиакомпаний: «Победа» и «Smartavia» — и создаёт подписку на выбранный маршрут. После этого он будет отправлять сообщения об изменении цен.
        TEXT;

        $this->telegram->send($this->method, [
            'chat_id' => $this->fromId,
            'text'    => $text,
        ]);
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    protected function parseDto(DtoContract $dto): void
    {
    }
}