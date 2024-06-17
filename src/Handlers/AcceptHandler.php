<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Handler;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final readonly class AcceptHandler extends Handler
{
    private string $state;
    private string $dep;
    private string $arr;
    private string $month;
    private string $year;
    private string $day;

    public static function validate(DtoContract $dto): bool
    {
        return preg_match('/^(sel_acc|suc):[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}:\d{1,2}$/', $dto->data) === 1;
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
        $data = explode(':', $dto->data);
        $data[3] = $this->formatNums($data[3]);
        $data[5] = $this->formatNums($data[5]);
        [
            $this->state,
            $this->dep,
            $this->arr,
            $this->month,
            $this->year,
            $this->day,
        ] = $data;
    }

    private function formatNums(string $num): string
    {
        return (int) $num < 10 ? '0' . $num : $num;
    }

    private function getMessageData(Airport $depAirport, Airport $arrAirport): array
    {
        $data = [
            'chat_id'    => $this->fromId,
            'message_id' => $this->messageId,
        ];
        $text = '';
        if ($this->state === 'suc') {
            $text .= "Мониторинг успешно активирован!\n";
        } else {
            $data['reply_markup'] = [
                'inline_keyboard' => [
                    $this->getSuccessButton(),
                ],
            ];
        }

        $text .= <<<TEXT
Город отправления $depAirport->title ($depAirport->code)
Город прибытия $arrAirport->title ($arrAirport->code)
Дата вылета $this->day.$this->month.$this->year
TEXT;
        $data['text'] = $text;
        return $data;
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
                'callback_data' => "suc:$this->dep:$this->arr:$this->month:$this->year:$this->day",
            ],
        ];
    }
}