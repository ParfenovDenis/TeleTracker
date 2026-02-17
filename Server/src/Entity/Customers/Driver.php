<?php
/**
 * @license  AVT
 */

namespace App\Entity\Customers;

use App\Entity\Relation\DriverTruck;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Customers\DriverRepository")
 */
class Driver
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
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $middleName;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\Length(
     *     min = 10,
     *     max = 10,
     *     exactMessage="length_driver_license"
     * )
     */
    private $driverLicense;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customers\Company", inversedBy="drivers")
     */
    private $company;

    /**
     * @ORM\Column(type="date")
     */
    private $DriverLicenseExpirationDate;

    /**
     * @ORM\Column(type="date")
     */
    private $employmentDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\Expression("this.getEmploymentDate() == null or this.getDismissalDate() ==  null or this.getEmploymentDate()<this.getDismissalDate()",message="employment_date_greater_than_dismissal_date")
     */
    private $dismissalDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relation\DriverTruck", mappedBy="driver")
     */
    private $driverTrucks;


    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;


    public function __construct()
    {
        $this->driverTrucks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getDriverLicense(): ?string
    {
        return $this->driverLicense;
    }

    public function setDriverLicense(string $driverLicense): self
    {
        $this->driverLicense = $driverLicense;

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

    public function getDriverLicenseExpirationDate(): ?\DateTimeInterface
    {
        return $this->DriverLicenseExpirationDate;
    }

    public function setDriverLicenseExpirationDate(\DateTimeInterface $DriverLicenseExpirationDate): self
    {
        $this->DriverLicenseExpirationDate = $DriverLicenseExpirationDate;

        return $this;
    }

    public function getEmploymentDate(): ?\DateTimeInterface
    {
        return $this->employmentDate;
    }

    public function setEmploymentDate(\DateTimeInterface $employmentDate): self
    {
        $this->employmentDate = $employmentDate;

        return $this;
    }

    public function getDismissalDate(): ?\DateTimeInterface
    {
        return $this->dismissalDate;
    }

    public function setDismissalDate(?\DateTimeInterface $dismissalDate): self
    {
        $this->dismissalDate = $dismissalDate;

        return $this;
    }

    public function getFIO(): string
    {
        $fio = ($this->getLastName() ?: '').' ';
        $fio .= ($this->getFirstName() ? \mb_substr($this->getFirstName(), 0, 1).'.' : '').' ';
        $fio .= ($this->getMiddleName() ? \mb_substr($this->getMiddleName(), 0, 1).'.' : '');

        return $fio;
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
            $driverTruck->setDriver($this);
        }

        return $this;
    }

    public function removeDriverTruck(DriverTruck $driverTruck): self
    {
        if ($this->driverTrucks->contains($driverTruck)) {
            $this->driverTrucks->removeElement($driverTruck);
            // set the owning side to null (unless already changed)
            if ($driverTruck->getDriver() === $this) {
                $driverTruck->setDriver(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    /**
     * @param mixed $isDeleted
     *
     * @return Driver
     */
    public function setIsDeleted(bool $isDeleted):self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }



    public function __toString()
    {
        return $this->lastName.' '.$this->firstName.' '.$this->middleName;
    }
}
