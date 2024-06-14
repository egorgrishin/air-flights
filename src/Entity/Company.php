<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\Table(name: 'companies')]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 63)]
    private ?string $title = null;

    /**
     * @var Collection<int, Airport>
     */
    #[ORM\ManyToMany(targetEntity: Airport::class, mappedBy: 'companies')]
    private Collection $airports;

    public function __construct()
    {
        $this->airports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Airport>
     */
    public function getAirports(): Collection
    {
        return $this->airports;
    }

    public function addAirport(Airport $airport): static
    {
        if (!$this->airports->contains($airport)) {
            $this->airports->add($airport);
            $airport->addCompany($this);
        }

        return $this;
    }

    public function removeAirport(Airport $airport): static
    {
        if ($this->airports->removeElement($airport)) {
            $airport->removeCompany($this);
        }

        return $this;
    }
}
