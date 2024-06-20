<?php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\SearcherContract;
use App\Core\Container;
use App\Searchers\PobedaSearch;
use App\Searchers\SmartaviaSearch;
use App\VO\CompanySubscription;
use DateTime;
use Throwable;

class GetPriceService
{
    /**
     * @var class-string<SearcherContract>[]
     */
    private const SEARCHERS = [
        PobedaSearch::class,
        SmartaviaSearch::class,
    ];

    public function run(int $subscriptionId, string $dep, string $arr, DateTime $dt): array
    {
        $data = [];
        foreach (self::SEARCHERS as $searcher) {
            $searcher = new $searcher();
            $data[] = new CompanySubscription(
                $searcher->getCode(),
                $subscriptionId,
                $this->getSearcherPrice($searcher, $dep, $arr, $dt),
            );
        }
        return $data;
    }

    private function getSearcherPrice(SearcherContract $searcher, string $dep, string $arr, DateTime $dt): ?float
    {
        for ($i = 0; $i < 5; $i++) {
            try {
                return $searcher->run($dep, $arr, $dt);
            } catch (Throwable $exception) {
                Container::logger()->error($exception);
                usleep(500_000);
            }
        }
        return null;
    }
}