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
     * @param string|null $excluded
     * @return Airport[]
     */
    public function getAll(int $offset, int $limit, ?string $excluded = null): array
    {
        $condition = $excluded ? 'WHERE code NOT LIKE :excluded' : '';
        $sql = <<<SQL
        SELECT code, city_code, sort, title
        FROM airports
        $condition
        ORDER BY sort IS NULL, sort, title
        LIMIT :offset, :limit
        SQL;

        $stmt = Container::pdo()->prepare($sql);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        if ($excluded) {
            $stmt->bindParam(':excluded', $excluded);
        }
        $stmt->execute();
        return $stmt->fetchAll(
            PDO::FETCH_FUNC,
            function (string $code, ?string $cityCode, ?int $sort, string $title) {
                return new Airport($code, $cityCode, $sort, $title);
            }
        );
    }

    public function getCount(?string $excluded = null): int
    {
        $condition = $excluded ? 'WHERE code NOT LIKE :excluded' : '';
        $stmt = Container::pdo()->prepare("SELECT COUNT(*) FROM airports $condition");
        if ($excluded) {
            $stmt->bindParam(':excluded', $excluded);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getByCode(array $codes): array
    {
        $questions = implode(',', array_fill(0, count($codes), '?'));
        $sql = <<<SQL
        SELECT code, city_code, sort, title
        FROM airports
        WHERE code IN ($questions)
        SQL;

        $stmt = Container::pdo()->prepare($sql);
        $stmt->execute($codes);

        return $stmt->fetchAll(
            PDO::FETCH_FUNC,
            function (string $code, ?string $cityCode, ?int $sort, string $title) {
                return new Airport($code, $cityCode, $sort, $title);
            }
        );
    }
}