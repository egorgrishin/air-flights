<?php

namespace App\Entity;

use App\Repository\AirportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AirportRepository::class)]
#[ORM\Table(name: 'airports')]
class Airport
{
    #[ORM\Id]
    #[ORM\Column(length: 15)]
    private ?string $code = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $city_code = null;

    #[ORM\Column(length: 127)]
    private ?string $title = null;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'airports')]
    #[ORM\JoinColumn(name: 'airport_code', referencedColumnName: 'code', nullable: false)]
    private Collection $companies;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getCityCode(): ?string
    {
        return $this->city_code;
    }

    public function setCityCode(?string $city_code): static
    {
        $this->city_code = $city_code;

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
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        $this->companies->removeElement($company);

        return $this;
    }
}
