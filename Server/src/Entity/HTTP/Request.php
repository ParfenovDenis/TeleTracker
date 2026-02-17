<?php
/**
 * @license AVT
 */

namespace App\Entity\HTTP;

use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;
use App\Model\AbstractObjectBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\HTTP\RequestRepository")
 * @ORM\Table(indexes={@ORM\Index(name="is_processed_idx", fields={"isProcessed"})})
 */
class Request
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isProcessed = false;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $imei;

    /**
     * @ORM\Column(type="integer")
     */
    private $fp;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetime;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CanBus\Log\Line", mappedBy="requestId")
     */
    private $logs;


    /**
     * @ORM\Column(type="binary", length=9983)
     */
    private $content;

    /**
     * @ORM\Column(type="integer", options={"default": 1})
     */
    private $version;

    public function __construct()
    {
        $this->logs = new ArrayCollection();
        $this->modems = new ArrayCollection();
        $this->GPS = new ArrayCollection();
        $this->canBusMessages = new ArrayCollection();
        $this->setDatetime(new \DateTime());
    }

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

    public function getFp(): ?int
    {
        return $this->fp;
    }

    public function setFp(int $fp): self
    {
        $this->fp = $fp;

        return $this;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * @return Collection|Line[]
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Line $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setRequestId($this);
        }

        return $this;
    }

    public function removeLog(Line $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getRequestId() === $this) {
                $log->setRequestId(null);
            }
        }

        return $this;
    }

    public function clearLogs ()
    {
        $this->logs = new ArrayCollection();
    }


    public function setImeiBytes($bytes): self
    {
        $imei = self::convertImei($bytes);
        $this->setImei($imei);
        return $this;
    }

    public static function convertImei($bytes): string
    {
        $imei1 = (ord($bytes[0]) | (ord($bytes[1]) >> 6 << 8));
        $byte1 = ord($bytes[1]);
        if ($byte1 >= 128)
            $byte1 -= 128;
        if ($byte1 >= 64)
            $byte1 -= 64;
        $imei2 = ord($bytes[2]) << 16 | ord($bytes[3]) << 8 | ord($bytes[4]) | $byte1 << 24;
        $imei = "86" . (string)$imei1 . (string)$imei2;
        return $imei;
    }

    public function setFpBytes($bytes): self
    {
        $this->setFp(ord($bytes[0]) << 24 | ord($bytes[1]) << 16 | ord($bytes[2]) << 8 | ord($bytes[3]));
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        if (is_resource($this->content)) {
            if ($this->version > 0 && class_exists(($class = 'App\Model\ObjectBuilderV' . $this->getVersion())))
                $length = $class::POST_DATA_SIZE;
            else
                $length = AbstractObjectBuilder::POST_DATA_SIZE;
            $this->content = fread($this->content, $length);
        }
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function isIsProcessed(): ?bool
    {
        return $this->isProcessed;
    }

    public function setIsProcessed(bool $isProcessed): self
    {
        $this->isProcessed = $isProcessed;

        return $this;
    }
}
