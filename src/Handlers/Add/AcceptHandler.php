<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final readonly class AcceptHandler extends Add
{
    private const PREV = State::SelectDay->value;
    private const SELF = State::AcceptMonitoring->value;
    private const NEXT = State::SuccessMonitoring->value;
    private string $dep;
    private string $arr;
    private string $month;
    private string $year;
    private string $day;

    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
        return preg_match("/^$state:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}:\d{1,2}$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $airports = (new AirportRepository())->getByCode([$this->dep, $this->arr]);
        $depAirport = $this->getAirportByCode($this->dep, $airports);
        $arrAirport = $this->getAirportByCode($this->arr, $airports);
        $data = $this->getMessageData($depAirport, $arrAirport);

        $this->telegram->send($this->method, $data);
    }

    protected function parseDto(DtoContract $dto): void
    {
        [
            ,
            $this->dep,
            $this->arr,
            $month,
            $this->year,
            $day,
        ] = explode(':', $dto->data);
        $this->month = $this->formatNum($month);
        $this->day = $this->formatNum($day);
    }

    private function formatNum(string $num): string
    {
        return (int) $num < 10 ? '0' . $num : $num;
    }

    private function getMessageData(Airport $depAirport, Airport $arrAirport): array
    {
        $text = <<<TEXT
        Город отправления $depAirport->title ($depAirport->code)
        Город прибытия $arrAirport->title ($arrAirport->code)
        Дата вылета $this->day.$this->month.$this->year
        TEXT;

        return [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    $this->getSuccessButton(),
                    $this->getMenuButtons(),
                ],
            ],
        ];
    }

    private function getAirportByCode(string $code, array $airports): Airport
    {
        return array_values(
                   array_filter($airports, fn (Airport $airport) => $airport->code === $code)
               )[0];
    }

    private function getSuccessButton(): array
    {
        return [
            [
                'text'          => 'Подтвердить',
                'callback_data' => self::NEXT . ":$this->dep:$this->arr:$this->month:$this->year:$this->day",
            ],
        ];
    }

    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":$this->dep:$this->arr:$this->month:$this->year";
    }
}