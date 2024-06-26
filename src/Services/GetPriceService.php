<?php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\SearcherContract;
use App\Core\Container;
use App\Exceptions\SearcherParseError;
use App\Exceptions\SearcherResponseError;
use App\Searchers\PobedaSearch;
use App\Searchers\SmartaviaSearch;
use App\VO\Price;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Throwable;

class GetPriceService
{
    /**
     * Классы, осуществляющие получение цен из API авиакомпаний
     *
     * @var class-string<SearcherContract>[]
     */
    private const SEARCHERS = [
        PobedaSearch::class,
        SmartaviaSearch::class,
    ];

    /**
     * Возвращает список цен различных авиакомпаний для указанной подписки
     *
     * @param int $subscriptionId
     * @param string $dep
     * @param string $arr
     * @param DateTime $dt
     * @return Price[]
     */
    public function run(int $subscriptionId, string $dep, string $arr, DateTime $dt): array
    {
        $data = [];
        foreach (self::SEARCHERS as $searcher) {
            $searcher = new $searcher();
            $data[] = new Price(
                $searcher->getCode(),
                $subscriptionId,
                $this->getSearcherPrice($searcher, $dep, $arr, $dt),
            );
        }
        return $data;
    }

    /**
     * Возвращает цену на билет для указанной подписки.
     * В случае неудачи обращения к API перезапускается
     */
    private function getSearcherPrice(SearcherContract $searcher, string $dep, string $arr, DateTime $dt): ?float
    {
        for ($i = 0; $i < 5; $i++) {
            try {
                return $searcher->getPrice($dep, $arr, $dt);
            } catch (SearcherResponseError|GuzzleException $exception) {
                Container::logger()->error($exception->getMessage());
                usleep(500_000);
            } catch (SearcherParseError|Throwable) {
                return null;
            }
        }
        return null;
    }
}