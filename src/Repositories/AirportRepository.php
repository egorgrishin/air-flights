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

    public function getByCode(array $codes): array
    {
        $questions = implode(',', array_fill(0, count($codes), '?'));

        $pdo = Container::pdo();
        $stmt = $pdo->prepare("SELECT code, city_code, sort, title FROM airports WHERE code IN ($questions) ORDER BY sort IS NULL, sort");
        $stmt->execute($codes);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Airport::class);
        return $stmt->fetchAll();
    }
}