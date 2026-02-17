<?php

namespace App\Entity\Routing;

use App\Entity\Customers\Company;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Routing\WaypointRepository")
 */
class Waypoint
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Ignore
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Routing\Route", inversedBy="waypoints")
     * @ORM\JoinColumn(nullable=false)
     */
    private $route;

    /**
     * @Ignore
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Customers\Company", inversedBy="waypoints")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $documentId;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTime;

    /**
     * @ORM\Column(type="decimal", precision=26, scale=2, nullable=true)
     */
    private $totalInvoiceValue;

    const STATUSES = [
        0 => 'waypoint_status_delivery',
        1 => 'waypoint_status_shipment',
        2 => 'waypoint_status_shipped',
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }

    public function setRoute(?Route $route): self
    {
        $this->route = $route;

        return $this;
    }


    public function getDocumentId(): ?int
    {
        return $this->documentId;
    }

    public function setDocumentId(?int $documentId): self
    {
        $this->documentId = $documentId;

        return $this;
    }


    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getStatus(): ?string
    {
        return self::STATUSES[$this->status];
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(?\DateTimeInterface $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->getCompany()->getLat();
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->getCompany()->getLng();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getCompany()->getTitle();
    }

    public function getTotalInvoiceValue(): ?string
    {
        return $this->totalInvoiceValue;
    }

    public function setTotalInvoiceValue(?string $totalInvoiceValue): self
    {
        $this->totalInvoiceValue = $totalInvoiceValue;

        return $this;
    }
}
