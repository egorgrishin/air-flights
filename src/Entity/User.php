<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $tg_id = null;

    public function getTgId(): ?string
    {
        return $this->tg_id;
    }

    public function setTgId(string $tg_id): static
    {
        $this->tg_id = $tg_id;

        return $this;
    }
}
