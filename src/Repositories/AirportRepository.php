<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;
use App\VO\Airport;
use PDO;

final class AirportRepository
{
    public function getAll(): array
    {
        $pdo = Container::pdo();
        $stmt = $pdo->query("SELECT code, city_code, sort, title FROM airports ORDER BY sort IS NULL, sort");
        $stmt->setFetchMode(PDO::FETCH_CLASS, Airport::class);
        return $stmt->fetchAll();
    }
}