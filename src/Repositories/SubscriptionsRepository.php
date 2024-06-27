<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;
use App\VO\Subscription;
use PDO;

class SubscriptionsRepository
{
    /**
     * Возвращает список активных подписок пользователя с их минимальными ценами
     *
     * @param string $chatId
     * @param int $offset
     * @param int $limit
     * @return Subscription[]
     */
    public function getChatSubscriptions(string $chatId, int $offset, int $limit): array
    {
        $sql = <<<SQL
            SELECT s.id, chat_id, dep.title, arr.title, date, p.min_price
            FROM subscriptions s
            JOIN (
                SELECT p.subscription_id, MIN(p.price) AS min_price
                FROM prices p
                GROUP BY p.subscription_id
            ) p ON s.id = p.subscription_id
            JOIN airports dep ON s.dep_code = dep.code
            JOIN airports arr ON s.arr_code = arr.code
            WHERE chat_id = :chatId AND is_active = 1
            ORDER BY date, s.id
            LIMIT :offset, :limit;
        SQL;

        $stmt = Container::pdo()->prepare($sql);
        $stmt->bindParam(':chatId', $chatId);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_FUNC,
            function (int $id, string $chatId, string $dep, string $arr, string $date, ?float $minPrice): Subscription {
                return new Subscription(
                    $chatId,
                    $date,
                    $id,
                    minPrice: $minPrice,
                    depTitle: $dep,
                    arrTitle: $arr,
                );
            },
        );
    }

    /**
     * Возвращает список всех активных подписок пользователя
     *
     * @return Subscription[]
     */
    public function get(): array
    {
        $sql = <<<SQL
            SELECT id, chat_id, dep_code, arr_code, date
            FROM subscriptions
            WHERE is_active = 1
            ORDER BY date, id
        SQL;

        return Container::pdo()
            ->query($sql)
            ->fetchAll(
                PDO::FETCH_FUNC,
                function (int $id, string $chatId, string $dep, string $arr, string $date): Subscription {
                    return new Subscription($chatId, $date, $id, $dep, $arr);
                },
            );
    }

    /**
     * Возвращает количество активных подписок пользователя
     *
     * @param string $chatId
     * @return int
     */
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

    /**
     * Создает новую подписку
     *
     * @param Subscription $subscription
     * @return int
     */
    public function create(Subscription $subscription): int
    {
        $sql = <<<SQL
            INSERT INTO subscriptions (chat_id, dep_code, arr_code, date)
            VALUES (?, ?, ?, ?)
        SQL;

        ($pdo = Container::pdo())
            ->prepare($sql)
            ->execute([
                $subscription->chatId,
                $subscription->depCode,
                $subscription->arrCode,
                $subscription->date,
            ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Блокирует истекшие подписки
     *
     * @return void
     */
    public function blockOldSubscriptions(): void
    {
        $sql = <<<SQL
            UPDATE subscriptions
            SET is_active = 0
            WHERE date < NOW()
        SQL;

        Container::pdo()
            ->prepare($sql)
            ->execute();
    }

    /**
     * Блокирует подписки по ID
     *
     * @param int $id
     * @param string $chatId
     * @return void
     */
    public function blockSubscriptionById(int $id, string $chatId): void
    {
        $sql = <<<SQL
            UPDATE subscriptions
            SET is_active = 0
            WHERE id = ?
              AND chat_id = ?
        SQL;

        Container::pdo()
            ->prepare($sql)
            ->execute([$id, $chatId]);
    }

    /**
     * Проверяет, что подписка с данными настройками у пользователя уже существует
     *
     * @param string $chatId
     * @param string $dep
     * @param string $arr
     * @param string $date
     * @return bool
     */
    public function isSubscriptionExists(string $chatId, string $dep, string $arr, string $date): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM subscriptions
            WHERE is_active = 1
              AND chat_id = ?
              AND dep_code = ?
              AND arr_code = ?
              AND date = ?
        SQL;

        $stmt = Container::pdo()->prepare($sql);
        $stmt->execute([$chatId, $dep, $arr, $date]);
        return $stmt->fetchColumn() === 1;
    }
}