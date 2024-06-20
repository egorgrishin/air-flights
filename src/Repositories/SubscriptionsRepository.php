<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;
use App\VO\Price;
use App\VO\Subscription;
use PDO;

class SubscriptionsRepository
{
    public function getAll(): array
    {
        return Container::pdo()
            ->query("SELECT id, chat_id, dep_code, arr_code, date FROM subscriptions WHERE is_active = 1 ORDER BY date")
            ->fetchAll(PDO::FETCH_FUNC, Subscription::fromPdo());
    }

    public function getAllPrices(array $subscriptionIds): array
    {
        $questions = implode(',', array_fill(0, count($subscriptionIds), '?'));

        $stmt = Container::pdo()
            ->prepare("
SELECT prices.*
FROM prices
JOIN subscriptions ON subscriptions.id = prices.subscription_id
WHERE 
    subscription_id IN ($questions) AND
    is_active = 1
");
        $stmt->execute($subscriptionIds);
        return $stmt->fetchAll(PDO::FETCH_FUNC, Price::fromPdo());
    }

    public function create(string $chatId, string $dep, string $arr, string $date): int
    {
        $sql = "INSERT INTO subscriptions (chat_id, dep_code, arr_code, date) VALUES (?, ?, ?, ?)";
        ($pdo = Container::pdo())
            ->prepare($sql)
            ->execute([$chatId, $dep, $arr, $date]);
        return (int) $pdo->lastInsertId();
    }

    public function createPrice(array $prices): void
    {
        $values = str_repeat('(?, ?, ?), ', count($prices) - 1) . '(?, ?, ?)';
        $sql = "INSERT INTO prices (company_code, subscription_id, price) VALUES $values";
        Container::pdo()
            ->prepare($sql)
            ->execute(
                array_merge(...array_map(fn (Price $price) => array_values($price->toArray()), $prices))
            );
    }
}