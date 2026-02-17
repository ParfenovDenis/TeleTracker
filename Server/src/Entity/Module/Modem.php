<?php
/**
 * @license AVT
 */

namespace App\Entity\Module;

use App\Entity\CanBus\Log\Line;
use App\Entity\HTTP\Request;
use App\Model\Converter;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Module\ModemRepository")
 */
class Modem
{
    /**
     * @ORM\Id()
     * @ORM\OneToOne(targetEntity="App\Entity\CanBus\Log\Line", inversedBy="modem")
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id")
     */
    private $log;

    /**
     * @ORM\Column(type="integer")
     */
    protected $rssi;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ber;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $millis;

    /**
     * @ORM\Column(type="integer")
     */
    protected $MemoryFree;

    public function __construct(Line $log, $millis = null)
    {
        $this->log = $log;

        $this->millis = $millis;
    }

    public function getLog(): ?Line
    {
        return $this->log;
    }

    public function setLog(Line $log): self
    {
        $this->log = $log;

        return $this;
    }

    public function getRssi()
    {
        if (is_resource($this->rssi)) {
            $this->rssi = ord(fread($this->rssi, 1));
        }
        return $this->rssi;
    }

    public function setRssi($rssi): self
    {
        $this->rssi = $rssi;

        return $this;
    }

    public function getBer()
    {
        if (is_resource($this->ber)) {
            $this->ber = ord(fread($this->ber, 1));
        }
        return $this->ber;
    }

    public function setBer($ber): self
    {
        $this->ber = $ber;

        return $this;
    }

    public function getMillis()
    {
        if (is_resource($this->millis)) {
            $this->millis = Converter::convertCharsToLong(fread($this->millis, 4));
        }
        return $this->millis;
    }

    public function setMillis($millis): self
    {
        $this->millis = $millis;

        return $this;
    }

    public function getMemoryFree()
    {
        if (is_resource($this->MemoryFree)) {
            $this->MemoryFree = Converter::convertCharsToLong(fread($this->MemoryFree, 2));
        }
        return $this->MemoryFree;
    }

    public function setMemoryFree($MemoryFree): self
    {
        $this->MemoryFree = $MemoryFree;

        return $this;
    }
}
