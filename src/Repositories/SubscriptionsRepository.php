<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;
use App\VO\Price;
use App\VO\Subscription;
use PDO;

class SubscriptionsRepository
{
    public function getChatSubscriptions(string $chatId, int $offset, int $limit): array
    {
        $sql = <<<SQL
        SELECT s.id, chat_id, dep_code, arr_code, date, p.min_price
        FROM subscriptions s
        JOIN (
            SELECT p.subscription_id, MIN(p.price) AS min_price
            FROM prices p
            GROUP BY p.subscription_id
        ) p ON s.id = p.subscription_id
        WHERE chat_id = :chatId AND is_active = 1
        ORDER BY date, s.id
        LIMIT :offset, :limit;
        SQL;

        $stmt = Container::pdo()->prepare($sql);
        $stmt->bindParam(':chatId', $chatId);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_FUNC, Subscription::fromPdoByChat());
    }

    public function getChatSubscriptionsCount(string $chatId): int
    {
        $sql = <<<SQL
        SELECT COUNT(*)
        FROM subscriptions
        WHERE chat_id = ? AND is_active = 1
        SQL;

        $stmt = Container::pdo()->prepare($sql);
        $stmt->execute([$chatId]);
        return $stmt->fetchColumn();
    }

    public function getAll(): array
    {
        return Container::pdo()
            ->query("SELECT id, chat_id, dep_code, arr_code, date FROM subscriptions WHERE is_active = 1 ORDER BY date")
            ->fetchAll(PDO::FETCH_FUNC, Subscription::fromPdoAll());
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

    public function blockOldSubscriptions(): void
    {
        $sql = "UPDATE subscriptions SET is_active = 0 WHERE date < NOW()";
        Container::pdo()
            ->prepare($sql)
            ->execute();
    }

    public function blockSubscriptionById(int $id, string $chatId): void
    {
        Container::pdo()
            ->prepare("UPDATE subscriptions SET is_active = 0 WHERE id = ? AND chat_id = ?")
            ->execute([$id, $chatId]);
    }

    public function createPrices(array $prices): void
    {
        $values = str_repeat('(?, ?, ?), ', count($prices) - 1) . '(?, ?, ?)';
        $sql = "INSERT INTO prices (company_code, subscription_id, price) VALUES $values";
        Container::pdo()
            ->prepare($sql)
            ->execute(
                array_merge(...array_map(fn (Price $price) => array_values($price->toArray()), $prices))
            );
    }

    public function updatePrice(Price $price): void
    {
        $sql = "UPDATE prices SET price = ? WHERE company_code = ? AND subscription_id = ?";
        Container::pdo()
            ->prepare($sql)
            ->execute([$price->price, $price->companyCode, $price->subscriptionId]);
    }
}