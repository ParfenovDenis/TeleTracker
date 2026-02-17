<?php
/**
 * @license AVT
 */

namespace App\Entity\Customers;

use App\Entity\Relation\CompanyContragent;
use App\Entity\Routing\Waypoint;
use App\Entity\Security\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Customers\CompanyRepository")
 */
class Company
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Security\User", inversedBy="companies")
     */
    private $user;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $officialName;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $inn;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $manager;

    /**
     * @ORM\Column(type="text")
     */
    private $address;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=6, nullable=true)
     * @var float
     */
    private $lat;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     * @var float
     */
    private $lng;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     */
    private $radius;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customers\GroupCompanies", inversedBy="companies")
     */
    private $groupCompanies;

    /**
     * @ORM\OneToMany(targetEntity="Company", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customers\Driver", mappedBy="company")
     */
    private $drivers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customers\Truck", mappedBy="company")
     */
    private $trucks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Routing\Waypoint", mappedBy="company")
     */
    private $waypoints;

    /**
     * @ORM\OneToOne(targetEntity=CompanyContragent::class, mappedBy="company", cascade={"persist", "remove"})
     */
    private $contragent;



    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->drivers = new ArrayCollection();
        $this->trucks = new ArrayCollection();
        $this->waypoints = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->contains($user)) {
            $this->user->removeElement($user);
        }

        return $this;
    }


    public function getOfficialName(): ?string
    {
        return $this->officialName;
    }

    public function setOfficialName(string $officialName): self
    {
        $this->officialName = $officialName;

        return $this;
    }

    public function getInn(): ?string
    {
        return $this->inn;
    }

    public function setInn(string $inn): self
    {
        $this->inn = $inn;

        return $this;
    }

    public function getManager(): ?string
    {
        return $this->manager;
    }

    public function setManager(?string $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return float
     */
    public function getLat():?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return float
     */
    public function getLng():?float
    {
        return $this->lng;
    }

    public function setLng(?float $lng): self
    {
        $this->lng = $lng;

        return $this;
    }


    public function getRadius()
    {
        return $this->radius;
    }

    public function setRadius($radius): self
    {
        $this->radius = $radius;

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getGroupCompanies(): ?GroupCompanies
    {
        return $this->groupCompanies;
    }

    public function setGroupCompanies(?GroupCompanies $groupCompanies): self
    {
        $this->groupCompanies = $groupCompanies;

        return $this;
    }

    public function setParent(?Company $company): self
    {
        $this->parent = $company;

        return $this;
    }

    public function getParent(): ?Company
    {
        return $this->parent;
    }

    /**
     * @return Collection|Company[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChildren(Company $company): self
    {
        if (!$this->children->contains($company)) {
            $this->children[] = $company;
            $company->setParent($this);
        }

        return $this;
    }

    public function removeChildren(Company $company): self
    {
        if ($this->children->contains($company)) {
            $this->children->removeElement($company);
            if ($company->getParent() === $this) {
                $company->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Driver[]
     */
    public function getDrivers(): Collection
    {
        return $this->drivers;
    }

    public function addDriver(Driver $driver): self
    {
        if (!$this->drivers->contains($driver)) {
            $this->drivers[] = $driver;
            $driver->setCompany($this);
        }

        return $this;
    }

    public function removeDriver(Driver $driver): self
    {
        if ($this->drivers->contains($driver)) {
            $this->drivers->removeElement($driver);
            // set the owning side to null (unless already changed)
            if ($driver->getCompany() === $this) {
                $driver->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Truck[]
     */
    public function getTrucks(): Collection
    {
        return $this->trucks;
    }

    public function addTruck(Truck $truck): self
    {
        if (!$this->trucks->contains($truck)) {
            $this->trucks[] = $truck;
            $truck->setCompany($this);
        }

        return $this;
    }

    public function removeTruck(Truck $truck): self
    {
        if ($this->trucks->contains($truck)) {
            $this->trucks->removeElement($truck);
            // set the owning side to null (unless already changed)
            if ($truck->getCompany() === $this) {
                $truck->setCompany(null);
            }
        }

        return $this;
    }

    public function addChild(Company $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Company $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Waypoint[]
     */
    public function getWaypoints(): Collection
    {
        return $this->waypoints;
    }

    public function addWaypoint(Waypoint $waypoint): self
    {
        if (!$this->waypoints->contains($waypoint)) {
            $this->waypoints[] = $waypoint;
            $waypoint->setCompany($this);
        }

        return $this;
    }

    public function removeWaypoint(Waypoint $waypoint): self
    {
        if ($this->waypoints->contains($waypoint)) {
            $this->waypoints->removeElement($waypoint);
            // set the owning side to null (unless already changed)
            if ($waypoint->getCompany() === $this) {
                $waypoint->setCompany(null);
            }
        }

        return $this;
    }

    public function getContragent(): ?CompanyContragent
    {
        return $this->contragent;
    }

    public function setContragent(CompanyContragent $contragent): self
    {
        // set the owning side of the relation if necessary
        if ($contragent->getCompany() !== $this) {
            $contragent->setCompany($this);
        }

        $this->contragent = $contragent;

        return $this;
    }

}
