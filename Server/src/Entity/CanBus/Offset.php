<?php
/**
 * @license AVT
 */
namespace App\Entity\CanBus;

use App\Entity\Customers\Truck;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CanBus\OffsetRepository")
 * @ORM\Table(name="offset", indexes={@ORM\Index(name="IDX_truck_datetime", columns={"truck_id","dateTimeFrom","dateTimeTo"})})
 */
class Offset
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customers\Truck", inversedBy="offsets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $truck;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateTimeFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTimeTo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $distance;

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->dateTimeFrom;
    }

    public function setDateTimeFrom(\DateTimeInterface $dateTimeFrom): self
    {
        $this->dateTimeFrom = $dateTimeFrom;

        return $this;
    }

    public function getDateTimeTo(): ?\DateTimeInterface
    {
        return $this->dateTimeTo;
    }

    public function setDateTimeTo(?\DateTimeInterface $dateTimeTo): self
    {
        $this->dateTimeTo = $dateTimeTo;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }
}
