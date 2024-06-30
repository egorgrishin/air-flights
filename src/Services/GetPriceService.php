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
use Exception;
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
            try {
                $searcher = new $searcher();
                $price = $this->getSearcherPrice($searcher, $dep, $arr, $dt);
            } catch (Throwable) {
                $message = "5 retries: {$searcher->getCode()}, $dep, $arr, {$dt->format('Y-m-d')}";
                Container::logger()->error($message);
                continue;
            }

            $data[$searcher->getCode()] = new Price(
                $searcher->getCode(),
                $subscriptionId,
                $price,
            );
        }
        return $data;
    }

    /**
     * Возвращает цену на билет для указанной подписки.
     * В случае неудачи обращения к API перезапускается
     * @throws Exception
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
        throw new Exception();
    }
}