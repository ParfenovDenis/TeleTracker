<?php

namespace App\Entity\Relation;

use App\Entity\Customers\Company;
use App\Repository\Relation\CompanyContragentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompanyContragentRepository::class)
 * @ORM\Table(name="company_contragent")
 */
class CompanyContragent
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="contragent_id")
     */
    private $contragentId;

    /**
     * @ORM\OneToOne(targetEntity=Company::class, inversedBy="contragent", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    public function getContragentId(): ?int
    {
        return $this->contragentId;
    }

    public function setContragentId(int $contragentId): self
    {
        $this->contragentId = $contragentId;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
