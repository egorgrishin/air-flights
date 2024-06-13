<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

class Company
{
    public function __construct(
        public string $title,
        public ?int   $id = null,
    ) {}

    public function create(): self
    {
        $pdo = DB::getInstance();
        $stmt = $pdo->prepare("INSERT INTO companies(title) VALUES (?)");
        $stmt->execute([$this->title]);

        $lastId = $pdo->lastInsertId();
        $this->id = $lastId ? (int) $lastId : null;
        return $this;
    }
}