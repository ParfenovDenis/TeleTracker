<?php
/**
 * @license AVT
 */

namespace App\Entity\CanBus;

use App\Entity\CanBus\Log\Line;
use App\Entity\HTTP\Request;
use Doctrine\ORM\Mapping as ORM;
use http\Exception\InvalidArgumentException;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CanBus\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\OneToOne(targetEntity="App\Entity\CanBus\Log\Line", inversedBy="message")
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id")
     *
     */
    private $log;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $fuel;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    private $fuelEconomy;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=3)
     */
    private $rpm;

    /**
     * @ORM\Column(type="integer")
     */
    private $temp;

    /**
     * @ORM\Column(type="integer")
     */
    private $distance;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=8)
     */
    private $speed;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $brakePedal;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $gasPedal;


    /**
     * @ORM\Column(type="integer")
     */
    private $weight;

    const MAX_RPM = 10000;

    public function getLog(): ?Line
    {
        return $this->log;
    }

    public function setLog(?Line $log): self
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFuel()
    {
        return $this->fuel;
    }

    /**
     * @param mixed $fuel
     *
     * @return Message
     */
    public function setFuel($fuel): self
    {
        $this->fuel = $fuel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFuelEconomy()
    {
        return $this->fuelEconomy;
    }

    /**
     * @param mixed $fuelEconomy
     *
     * @return Message
     */
    public function setFuelEconomy($fuelEconomy): self
    {
        $this->fuelEconomy = $fuelEconomy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRpm()
    {
        return $this->rpm;
    }

    /**
     * @param int $rpm
     *
     * @return $this
     *
     * @throws InvalidValueException
     */
    public function setRpm(int $rpm): self
    {
        if ($rpm >= self::MAX_RPM) {
            throw new InvalidValueException(sprintf("RPM greater then 10000: %s", $rpm));
        }
        $this->rpm = $rpm;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * @param mixed $temp
     *
     * @return Message
     */
    public function setTemp($temp): self
    {
        $this->temp = $temp;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param mixed $distance
     *
     * @return Message
     */
    public function setDistance($distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * @param mixed $speed
     * @return Message
     */
    public function setSpeed($speed): self
    {
        $this->speed = $speed;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrakePedal()
    {
        return $this->brakePedal;
    }

    /**
     * @param mixed $brakePedal
     * @return Message
     */
    public function setBrakePedal($brakePedal): self
    {
        $this->brakePedal = $brakePedal;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGasPedal()
    {
        return $this->gasPedal;
    }

    /**
     * @param mixed $gasPedal
     * @return Message
     */
    public function setGasPedal($gasPedal): self
    {
        $this->gasPedal = $gasPedal;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     * @return Message
     */
    public function setWeight($weight): self
    {
        $this->weight = $weight;
        return $this;
    }


}
