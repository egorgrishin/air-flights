<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Container;
use App\VO\Company;
use PDO;

final class CompanyRepository
{
    public function getAll(): array
    {
        $pdo = Container::pdo();
        $stmt = $pdo->query("SELECT id, title FROM companies");
        $stmt->setFetchMode(PDO::FETCH_CLASS, Company::class);
        return $stmt->fetchAll();
    }
}