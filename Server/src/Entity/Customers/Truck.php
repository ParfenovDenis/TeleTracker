<?php
/**
 * @license AVT
 */
namespace App\Entity\Customers;


use App\Entity\CanBus\Offset;
use App\Entity\Relation\DriverTruck;
use App\Entity\Routing\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Customers\TruckRepository")
 */
class Truck
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $numberPlate;

    /**
     * @ORM\Column(type="string", length=17)
     */
    private $vin;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $sts;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $pts;

    /**
     * @ORM\Column(type="string", length=9)
     */
    private $fuelCard;

    /**
     * @ORM\Column(type="datetime")
     */
    private $DateTimeIn;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $DateTimeOut;

    /**
     * @ORM\Column(type="smallint")
     */
    private $fuelFlowRate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customers\ImeiTruck", mappedBy="truck", orphanRemoval=true)
     */
    private $imei;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customers\Company", inversedBy="trucks")
     */
    private $company;

    /**
     * @ORM\Column(type="smallint")
     */
    private $fuelTank;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relation\DriverTruck", mappedBy="truck")
     */
    private $driverTrucks;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $icon;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CanBus\Offset", mappedBy="truck", orphanRemoval=true)
     */
    private $offsets;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Routing\Route", mappedBy="truck")
     */
    private $routes;

    public function __construct()
    {
        $this->imei = new ArrayCollection();
        $this->imeis = new ArrayCollection();
        $this->driverTrucks = new ArrayCollection();
        $this->offsets = new ArrayCollection();
        $this->routes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumberPlate(): ?string
    {
        return $this->numberPlate;
    }

    public function setNumberPlate(?string $numberPlate): self
    {
        $this->numberPlate = $numberPlate;

        return $this;
    }

    public function __toString()
    {
        return $this->brand . ' ' . $this->model . ' ' . $this->numberPlate;
    }


    /**
     * @return Collection|ImeiTruck[]
     */
    public function getImei(): Collection
    {
        return $this->imei;
    }

    public function addImei(ImeiTruck $imei): self
    {
        if (!$this->imei->contains($imei)) {
            $this->imei[] = $imei;
            $imei->setTruck($this);
        }

        return $this;
    }

    public function removeImei(ImeiTruck $imei): self
    {
        if ($this->imei->contains($imei)) {
            $this->imei->removeElement($imei);
            // set the owning side to null (unless already changed)
            if ($imei->getTruck() === $this) {
                $imei->setTruck(null);
            }
        }

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

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(string $vin): self
    {
        $this->vin = $vin;

        return $this;
    }

    public function getFuelTank(): ?int
    {
        return $this->fuelTank;
    }

    public function setFuelTank(int $fuelTank): self
    {
        $this->fuelTank = $fuelTank;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getTitle():string
    {
       return $this->brand . ' ' . $this->model . ' ' . $this->numberPlate;
    }

    public function getSts(): ?string
    {
        return $this->sts;
    }

    public function setSts(string $sts): self
    {
        $this->sts = $sts;

        return $this;
    }

    public function getPts(): ?string
    {
        return $this->pts;
    }

    public function setPts(string $pts): self
    {
        $this->pts = $pts;

        return $this;
    }

    public function getFuelCard(): ?string
    {
        return $this->fuelCard;
    }

    public function setFuelCard(string $fuelCard): self
    {
        $this->fuelCard = $fuelCard;

        return $this;
    }

    public function getDateTimeIn(): ?\DateTimeInterface
    {
        return $this->DateTimeIn;
    }

    public function setDateTimeIn(\DateTimeInterface $DateTimeIn): self
    {
        $this->DateTimeIn = $DateTimeIn;

        return $this;
    }

    public function getDateTimeOut(): ?\DateTimeInterface
    {
        return $this->DateTimeOut;
    }

    public function setDateTimeOut(?\DateTimeInterface $DateTimeOut): self
    {
        $this->DateTimeOut = $DateTimeOut;

        return $this;
    }

    public function getFuelFlowRate(): ?int
    {
        return $this->fuelFlowRate;
    }

    public function setFuelFlowRate(int $fuelFlowRate): self
    {
        $this->fuelFlowRate = $fuelFlowRate;

        return $this;
    }

    /**
     * @return Collection|DriverTruck[]
     */
    public function getDriverTrucks(): Collection
    {
        return $this->driverTrucks;
    }

    public function addDriverTruck(DriverTruck $driverTruck): self
    {
        if (!$this->driverTrucks->contains($driverTruck)) {
            $this->driverTrucks[] = $driverTruck;
            $driverTruck->setTruck($this);
        }

        return $this;
    }

    public function removeDriverTruck(DriverTruck $driverTruck): self
    {
        if ($this->driverTrucks->contains($driverTruck)) {
            $this->driverTrucks->removeElement($driverTruck);
            // set the owning side to null (unless already changed)
            if ($driverTruck->getTruck() === $this) {
                $driverTruck->setTruck(null);
            }
        }

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection|Offset[]
     */
    public function getOffsets(): Collection
    {
        return $this->offsets;
    }

    public function addOffset(Offset $offset): self
    {
        if (!$this->offsets->contains($offset)) {
            $this->offsets[] = $offset;
            $offset->setTruck($this);
        }

        return $this;
    }

    public function removeOffset(Offset $offset): self
    {
        if ($this->offsets->contains($offset)) {
            $this->offsets->removeElement($offset);
            // set the owning side to null (unless already changed)
            if ($offset->getTruck() === $this) {
                $offset->setTruck(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Route[]
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function addRoute(Route $route): self
    {
        if (!$this->routes->contains($route)) {
            $this->routes[] = $route;
            $route->setTruck($this);
        }

        return $this;
    }

    public function removeRoute(Route $route): self
    {
        if ($this->routes->contains($route)) {
            $this->routes->removeElement($route);
            // set the owning side to null (unless already changed)
            if ($route->getTruck() === $this) {
                $route->setTruck(null);
            }
        }

        return $this;
    }




}
