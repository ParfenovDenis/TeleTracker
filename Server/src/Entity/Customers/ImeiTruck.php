<?php
/**
 * @license AVT
 */
namespace App\Entity\Customers;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Customers\ImeiTruckRepository")
 */
class ImeiTruck
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $imei;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customers\Truck", inversedBy="imei")
     * @ORM\JoinColumn(nullable=false)
     */
    private $truck;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $DateTimeFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $DateTimeTo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImei(): ?string
    {
        return $this->imei;
    }

    public function setImei(string $imei): self
    {
        $this->imei = $imei;

        return $this;
    }

    public function getTruck(): ?Truck
    {
        return $this->truck;
    }

    public function setTruck(?Truck $truck): self
    {
        $this->truck = $truck;

        return $this;
    }

    public function getDateTimeFrom(): ?\DateTimeInterface
    {
        return $this->DateTimeFrom;
    }

    public function setDateTimeFrom(?\DateTimeInterface $DateTimeFrom): self
    {
        $this->DateTimeFrom = $DateTimeFrom;

        return $this;
    }

    public function getDateTimeTo(): ?\DateTimeInterface
    {
        return $this->DateTimeTo;
    }

    public function setDateTimeTo(?\DateTimeInterface $DateTimeTo): self
    {
        $this->DateTimeTo = $DateTimeTo;

        return $this;
    }
}
