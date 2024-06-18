<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;

class SubscriptionsRepository
{
    public function create(string $chatId, string $date): void
    {
        $sql = "INSERT INTO subscriptions (chat_id, date) VALUES (?, ?)";
        Container::pdo()
            ->prepare($sql)
            ->execute([$chatId, $date]);
    }
}