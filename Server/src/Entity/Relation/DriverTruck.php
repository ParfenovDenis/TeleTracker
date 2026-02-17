<?php
/**
 * @license AVT
 */
namespace App\Entity\Relation;

use App\Entity\Customers\Driver;
use App\Entity\Customers\Truck;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Relation\DriverTruckRepository")
 */
class DriverTruck
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customers\Driver", inversedBy="driverTrucks")
     */
    private $driver;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customers\Truck", inversedBy="driverTrucks")
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

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
}
