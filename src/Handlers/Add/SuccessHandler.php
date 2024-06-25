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
        $dep = $this->getAirportByCode($this->dep, $airports);
        $arr = $this->getAirportByCode($this->arr, $airports);

        $this->telegram->send(
            $this->method,
            $data = $this->getMessageData($dep, $arr),
        );

        $subscriptionId = $this->createSubscription();
        $prices = $this->getPrices($subscriptionId);
        $this->subscriptionsRepository->createPrices($prices);
        $minPrice = $this->getMinPrice($prices);
        if ($minPrice) {
            $this->sendPriceToMessage($minPrice, $data['text']);
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

    private function getMessageData(Airport $dep, Airport $arr): array
    {
        $text = <<<TEXT
        Мониторинг успешно активирован!
        Город отправления $dep->title ($dep->code)
        Город прибытия $arr->title ($arr->code)
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
        $airports = array_filter($airports, fn (Airport $airport) => $airport->code === $code);
        return array_values($airports)[0];
    }

    private function createSubscription(): int
    {
        return $this->subscriptionsRepository->create(
            (string) $this->fromId, $this->dep, $this->arr, $this->date
        );
    }

    private function getPrices(int $subscriptionId): array
    {
        $dt = DateTime::createFromFormat('Y-m-d', $this->date);
        return (new GetPriceService())->run($subscriptionId, $this->dep, $this->arr, $dt);
    }

    private function getMinPrice(array $prices): ?float
    {
        $prices = array_filter($prices, fn (Price $price) => $price->price !== null);
        return $prices ? min(array_column($prices, 'price')) : null;
    }

    private function sendPriceToMessage(float $minPrice, string $text): void
    {
        $this->telegram->send($this->method, [
            'chat_id'    => $this->fromId,
            'message_id' => $this->messageId,
            'text'       => $text . "\nЦена {$minPrice}р",
        ]);
    }

    protected function getPrevCbData(): ?string
    {
        return null;
    }
}