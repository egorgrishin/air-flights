<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;
use App\Repositories\SubscriptionsRepository;
use App\Services\GetPriceService;
use App\VO\Airport;
use App\VO\Price;
use DateTime;

final readonly class SuccessHandler extends Add
{
    private const SELF = State::SuccessMonitoring->value;
    private AirportRepository $airportRepository;
    private SubscriptionsRepository $subscriptionsRepository;
    private string $dep;
    private string $arr;
    private string $month;
    private string $year;
    private string $day;
    private string $date;

    public function __construct(DtoContract $dto)
    {
        $this->airportRepository = new AirportRepository();
        $this->subscriptionsRepository = new SubscriptionsRepository();
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
        return preg_match("/^$state:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}:\d{1,2}$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $airports = $this->airportRepository->getByCode([$this->dep, $this->arr]);
        $depAirport = $this->getAirportByCode($this->dep, $airports);
        $arrAirport = $this->getAirportByCode($this->arr, $airports);
        $data = $this->getMessageData($depAirport, $arrAirport);

        $this->telegram->send($this->method, $data);

        $subscriptionId = $this->createSubscription();
        $prices = $this->createPrices($subscriptionId);
        if ($prices) {
            $this->sendPriceToMessage($prices, $data['text']);
        }
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
        $this->date = "$this->year-$this->month-$this->day";
    }

    private function formatNum(string $num): string
    {
        return (int) $num < 10 ? '0' . $num : $num;
    }

    private function getMessageData(Airport $depAirport, Airport $arrAirport): array
    {
        $text = <<<TEXT
        Мониторинг успешно активирован!
        Город отправления $depAirport->title ($depAirport->code)
        Город прибытия $arrAirport->title ($arrAirport->code)
        Дата вылета $this->day.$this->month.$this->year
        TEXT;

        return [
            'chat_id'    => $this->fromId,
            'message_id' => $this->messageId,
            'text'       => $text,
        ];
    }

    private function getAirportByCode(string $code, array $airports): Airport
    {
        return array_values(
                   array_filter($airports, fn (Airport $airport) => $airport->code === $code)
               )[0];
    }

    private function createSubscription(): int
    {
        return $this->subscriptionsRepository->create(
            (string) $this->fromId, $this->dep, $this->arr, $this->date
        );
    }

    private function createPrices(int $subscriptionId): array
    {
        $dt = DateTime::createFromFormat('Y-m-d', $this->date);
        $prices = (new GetPriceService())->run($subscriptionId, $this->dep, $this->arr, $dt);
        $this->subscriptionsRepository->createPrices($prices);
        return array_filter($prices, fn (Price $price) => $price->price !== null);
    }

    private function sendPriceToMessage(array $prices, string $text): void
    {
        $min = min(array_column($prices, 'price'));
        $this->telegram->send($this->method, [
            'chat_id'    => $this->fromId,
            'message_id' => $this->messageId,
            'text'       => $text . "\nЦена {$min}р",
        ]);
    }

    protected function getPrevCbData(): ?string
    {
        return null;
    }
}