<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;
use App\Repositories\PriceRepository;
use App\Repositories\SubscriptionsRepository;
use App\Services\GetPriceService;
use App\VO\Airport;
use App\VO\Price;
use App\VO\Subscription;
use DateTime;

final readonly class SuccessHandler extends Add
{
    private const SELF = State::SuccessMonitoring->value;
    private AirportRepository $airportRepository;
    private SubscriptionsRepository $subscriptionsRepository;
    private PriceRepository $priceRepository;
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
        $this->priceRepository = new PriceRepository();
        parent::__construct($dto);
    }

    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
        return preg_match("/^$state:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}:\d{1,2}$/", $dto->data) === 1;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $this->telegram->send($this->method, $this->getMessageData());

        $airports = $this->airportRepository->getByCode([$this->dep, $this->arr]);
        $dep = $this->getAirportByCode($this->dep, $airports);
        $arr = $this->getAirportByCode($this->arr, $airports);
        $subscriptionId = $this->createSubscription();

        $prices = $this->getPrices($subscriptionId);
        $this->priceRepository->createPrices(array_values($prices));
        $minPrice = $this->getMinPrice($prices);

        $this->sendPriceToMessage($dep, $arr, $minPrice);
        $this->sendSuccessCallback();
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
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

    /**
     * Возвращает содержимое сообщения, которое будет отображено
     * во время создания подписки и получения цен на авиабилеты
     */
    private function getMessageData(): array
    {
        return [
            'chat_id'    => $this->fromId,
            'message_id' => $this->messageId,
            'text'       => "Идет создание подписки...",
        ];
    }

    /**
     * Возвращает аэропорт по его коду из массива аэропортов
     */
    private function getAirportByCode(string $code, array $airports): Airport
    {
        $airports = array_filter($airports, fn (Airport $airport) => $airport->code === $code);
        return array_values($airports)[0];
    }

    /**
     * Добавляет подписку в базу данных
     */
    private function createSubscription(): int
    {
        $subscription = new Subscription(
            chatId: $this->fromId,
            date: $this->date,
            depCode: $this->dep,
            arrCode: $this->arr,
        );
        return $this->subscriptionsRepository->create($subscription);
    }

    /**
     * Получает цены от авиакомпаний и возвращает их
     */
    private function getPrices(int $subscriptionId): array
    {
        $dt = DateTime::createFromFormat('Y-m-d', $this->date);
        return (new GetPriceService())->run($subscriptionId, $this->dep, $this->arr, $dt);
    }

    /**
     * Возвращает минимальную цену из списка
     */
    private function getMinPrice(array $prices): ?float
    {
        $prices = array_filter($prices, fn (Price $price) => $price->price !== null);
        return $prices ? min(array_column($prices, 'price')) : null;
    }

    /**
     * Отправляет сообщение с минимальной ценой на билеты.
     */
    private function sendPriceToMessage(Airport $dep, Airport $arr, ?float $minPrice): void
    {
        if ($minPrice) {
            $text = <<<TEXT
            Подписка успешно активирована ✅️
            Теперь вам будут приходить уведомления об изменении цен!
            
            🛫 Город отправления: $dep->title ($dep->code)
            🛬 Город прибытия: $arr->title ($arr->code)
            Дата вылета: $this->day.$this->month.$this->year
            
            💰Текущая цена на рейс: $minPrice ₽
            TEXT;
        } else {
            $text = <<<TEXT
            Упс! 😬
            К сожалению, в настоящее время цен на выбранный маршрут нет. Попробуйте поменять дату. Если на эту дату появиться цена, вам придёт уведомление!
            TEXT;
        }

        $this->telegram->send($this->method, [
            'chat_id'    => $this->fromId,
            'message_id' => $this->messageId,
            'text'       => $text,
        ]);
    }

    /**
     * Возвращает callback-data для кнопки "Назад"
     */
    protected function getPrevCbData(): ?string
    {
        return null;
    }
}