<?php
/**
 * @license AVT
 */

namespace App\Entity\Relation;

use App\Entity\CanBus\Log\Line;
use App\Entity\Report\Event;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Relation\EventLogRepository")
 * @ORM\Table(name="event_log", indexes={@ORM\Index(name="IDX_imei_event_datetime", columns={"imei","event","first_datetime","last_datetime"})})
 */
class EventLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     *
     * @ORM\Column(type="string", length=15)
     */
    private $imei;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CanBus\Log\Line", inversedBy="firstLogs")
     */
    private $firstLog;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CanBus\Log\Line", inversedBy="lastLogs")
     */
    private $lastLog;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Event")
     * @ORM\JoinColumn(nullable=false, name="event")
     */
    private $event;

    /**
     * @ORM\Column(type="datetime", name="first_datetime")
     */
    private $firstDateTime;

    /**
     * @ORM\Column(type="datetime", name="last_datetime")
     */
    private $lastDateTime;




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

    public function getLastLog(): ?Line
    {
        return $this->lastLog;
    }

    public function setLastLog(Line $lastLog): self
    {
        $this->lastLog = $lastLog;

        return $this;
    }

    public function getFirstLog(): ?Line
    {
        return $this->firstLog;
    }

    /**
     * @param Line $firstLog
     * @return EventLog
     */
    public function setFirstLog(Line $firstLog): self
    {
        $this->firstLog = $firstLog;
        return $this;
    }

    public function getFirstDateTime(): ?\DateTimeInterface
    {
        return $this->firstDateTime;
    }

    public function setFirstDateTime(\DateTimeInterface $firstDateTime): self
    {
        $this->firstDateTime = $firstDateTime;

        return $this;
    }

    public function getLastDateTime(): ?\DateTimeInterface
    {
        return $this->lastDateTime;
    }

    public function setLastDateTime(\DateTimeInterface $lastDateTime): self
    {
        $this->lastDateTime = $lastDateTime;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

}
