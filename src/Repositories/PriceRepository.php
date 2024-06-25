<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;
use App\VO\Price;
use PDO;

final class PriceRepository
{
    /**
     * Возвращает цены на все активные подписки
     *
     * @param int[] $subscriptionIds
     * @return Price[]
     */
    public function get(array $subscriptionIds): array
    {
        $questions = implode(',', array_fill(0, count($subscriptionIds), '?'));
        $sql = <<<SQL
            SELECT company_code, subscription_id, price
            FROM prices
            JOIN subscriptions ON subscriptions.id = prices.subscription_id
            WHERE subscription_id IN ($questions)
              AND is_active = 1
        SQL;

        $stmt = Container::pdo()->prepare($sql);
        $stmt->execute($subscriptionIds);

        return $stmt->fetchAll(
            PDO::FETCH_FUNC,
            function (string $companyCode, int $subscriptionId, ?float $price): Price {
                return new Price($companyCode, $subscriptionId, $price);
            },
        );
    }

    /**
     * Сохраняет цены на подписку
     *
     * @param Price[] $prices
     * @return void
     */
    public function createPrices(array $prices): void
    {
        $values = str_repeat('(?, ?, ?), ', count($prices) - 1) . '(?, ?, ?)';
        $prices = array_merge(
            ...array_map(fn (Price $price) => array_values($price->toArray()), $prices)
        );
        $sql = <<<SQL
            INSERT INTO prices (company_code, subscription_id, price)
            VALUES $values
        SQL;

        Container::pdo()
            ->prepare($sql)
            ->execute($prices);
    }

    /**
     * Обновляет цену на подписку
     *
     * @param Price $price
     * @return void
     */
    public function updatePrice(Price $price): void
    {
        $sql = <<<SQL
            UPDATE prices
            SET price = ?
            WHERE company_code = ?
              AND subscription_id = ?
        SQL;

        Container::pdo()
            ->prepare($sql)
            ->execute([$price->price, $price->companyCode, $price->subscriptionId]);
    }
}