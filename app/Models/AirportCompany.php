<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class AirportCompany
{
    public function __construct(
        public string $airport_code,
        public int    $company_id,
    ) {}

    public function create(): self
    {
        $pdo = DB::getInstance();
        $stmt = $pdo->prepare("INSERT INTO airport_company (airport_code, company_id) VALUES (?, ?)");
        $stmt->execute([$this->airport_code, $this->company_id]);
        return $this;
    }
}