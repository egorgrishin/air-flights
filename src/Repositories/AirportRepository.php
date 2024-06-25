<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;
use App\VO\Airport;
use PDO;

final class AirportRepository
{
    /**
     * @param int $offset
     * @param int $limit
     * @return Airport[]
     */
    public function getAll(int $offset, int $limit): array
    {
        $sql = <<<SQL
        SELECT code, city_code, sort, title
        FROM airports
        ORDER BY sort IS NULL, sort
        LIMIT :offset, :limit
        SQL;

        $stmt = Container::pdo()->prepare($sql);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(
            PDO::FETCH_FUNC,
            function (string $code, string $cityCode, ?int $sort, string $title) {
                return new Airport($code, $cityCode, $sort, $title);
            }
        );
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