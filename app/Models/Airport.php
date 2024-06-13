<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class Airport
{
    public function __construct(
        public $code,
        public $city_code,
        public $title,
    ) {}

    public function create(): self
    {
        $pdo = DB::getInstance();
        $stmt = $pdo->prepare("INSERT INTO airports(code, city_code, title) VALUES (?, ?, ?)");
        $stmt->execute([$this->code, $this->city_code, $this->title]);
        return $this;
    }
}