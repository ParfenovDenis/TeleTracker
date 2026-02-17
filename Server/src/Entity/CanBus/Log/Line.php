<?php
/**
 * @license AVT
 */

namespace App\Entity\CanBus\Log;

use App\Entity\CanBus\Message;
use App\Entity\HTTP\Request;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use App\Entity\Relation\EventLog;
use App\Model\Converter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CanBus\LogRepository")
 * @ORM\Table(name="log")
 */
class Line
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\HTTP\Request", inversedBy="logs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $requestId;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Modem", mappedBy="log", cascade={"persist", "remove"})
     */
    private $modem;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $millis;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CanBus\Message", mappedBy="log")
     */
    protected $message;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\GPS", mappedBy="log")
     */
    private $gps;


    const PARAMS = [
        'fuel' => 0xFC21,
        'fuelEconomy' => 0xF200,
        'rpm' => 0x400,
        'temperature' => 0xEE00,
        'distance' => 0xC1EE,
        'speed' => 0x6CEE,
        'brakePedal' => 0x10B,
        'gasPedal' => 0x300,
        'weight' => 0x700B,
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relation\EventLog", mappedBy="firstLog")
     */
    private $firstLogs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Relation\EventLog", mappedBy="lastLog")
     */
    private $lastLogs;

    /**
     * Line constructor.
     */
    public function __construct()
    {
        $this->firstLogs = new ArrayCollection();
        $this->lastLogs = new ArrayCollection();
    }


    /**
     * @param string $name
     * @return float|int|null
     */
    public function __call($name, $arguments)
    {
        if (array_key_exists($name, self::PARAMS)) {
            return $this->getValue(self::PARAMS[$name]);
        }
        return null;
    }



    public function getRequestId(): ?Request
    {
        return $this->requestId;
    }

    public function setRequestId(?Request $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModem(): ?Modem
    {
        return $this->modem;
    }

    public function setModem(?Modem $modem): self
    {
        $this->modem = $modem;
        // set (or unset) the owning side of the relation if necessary
        $newLog = $modem === null ? null : $this;
        if ($newLog !== $modem->getLog()) {
            $modem->setLog($newLog);
        }

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

    public function getMessage(): ?Message
    {
        if (is_resource($this->message)) {
            $this->message = fread($this->message, 15);
        }

        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;
        $newLog = $message === null ? null : $this;
        if ($newLog && $newLog !== $message->getLog()) {
            $message->setLog($newLog);
        }

        return $this;
    }

    public function getGps(): ?GPS
    {
        return $this->gps;
    }

    public function setGps(?GPS $gps): self
    {
        $this->gps = $gps;

        // set (or unset) the owning side of the relation if necessary
        $newLog = $gps === null ? null : $this;
        if ($newLog !== $gps->getLog()) {
            $gps->setLog($newLog);
        }

        return $this;
    }

    /**
     * @return Collection|EventLog[]
     */
    public function getFirstLogs(): Collection
    {
        return $this->firstLogs;
    }

    public function addFirstLog(EventLog $eventLog): self
    {
        if (!$this->firstLogs->contains($eventLog)) {
            $this->firstLogs[] = $eventLog;
            $eventLog->setFirstLog($this);
        }

        return $this;
    }

    public function removeFirstLog(EventLog $eventLog): self
    {
        if ($this->firstLogs->contains($eventLog)) {
            $this->firstLogs->removeElement($eventLog);
            // set the owning side to null (unless already changed)
            if ($eventLog->getFirstLog() === $this) {
                $eventLog->setFirstLog(null);
            }
        }

        return $this;
    }

    public function getLastLogs(): Collection
    {
        return $this->lastLogs;
    }

    public function addLastLog(EventLog $eventLog): self
    {
        if (!$this->lastLogs->contains($eventLog)) {
            $this->lastLogs[] = $eventLog;
            $eventLog->setLastLog($this);
        }

        return $this;
    }

    public function removeLastLog(EventLog $eventLog): self
    {
        if ($this->lastLogs->contains($eventLog)) {
            $this->lastLogs->removeElement($eventLog);
            // set the owning side to null (unless already changed)
            if ($eventLog->getLastLog() === $this) {
                $eventLog->setLastLog(null);
            }
        }

        return $this;
    }

    public function getValue($address)
    {
        $message = $this->getMessage();
        $value = 0;
        switch ($address) {
            case 0xFC21:
                $value = ord($message[0]) * 0.4;
                break;
            case 0xF200:
                $value = floor($this->convertToLong(1, 2) / 512);
                break;
            case 0x400:
                $value = $this->convertToLong2(3, 2) * 0.125;
                break;
            case 0xEE00:
                $value = ord($message[5]) - 40;
                break;
            case 0xC1EE:
                $value = $this->convertToLong2(6, 4) * 5 / 1000;
                break;
            case 0x6CEE:
                $value = (ord($message[10]) << 8) / 256;
                break;
            case 0x10B:
                $value = ord($message[11]) * 0.4;
                break;
            case 0x300:
                $value = ord($message[12]) * 0.4;
                break;
            case 0x700B:
                $value = $this->convertToLong2(13, 2) * 10;
                break;
        }

        return $value;
    }

    public function convertToLong2($offset, $length = 4)
    {
        $message = $this->getMessage();
        $num = 0;
        for ($i = 0; $i < $length; $i++)
            $num = $num | ord($message[$offset + $i]) << ($i * 8);

        return $num;
    }

    public function convertToLong($offset, $length = 4)
    {
        $message = $this->getMessage();
        $num = 0;
        for ($i = $length; $i > 0; $i--)
            $num = $num | ord($message[$offset + $length - $i]) << (($i - 1) * 8);

        return $num;
    }
}
