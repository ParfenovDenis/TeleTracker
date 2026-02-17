<?php
/**
 * @license AVT
 */

namespace App\Entity\Module;

use App\Entity\CanBus\Log\Line;
use App\Entity\HTTP\Request;
use Doctrine\ORM\Mapping as ORM;
use App\Model\Converter;

//@ORM\GeneratedValue()

/**
 * @ORM\Entity(repositoryClass="App\Repository\Module\GPSRepository")
 * @ORM\Table(name="gps", indexes={@ORM\Index(name="IDX_gps", columns={"datetime", "log_id"})})
 */
class GPS
{

    /**
     * @ORM\Id()
     * @ORM\OneToOne(targetEntity="App\Entity\CanBus\Log\Line", inversedBy="gps")
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id")
     *
     */
    private $log;

    /**
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $datetime;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=6, nullable=true)
     */
    protected $latitude;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     */
    protected $longitude;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $altitude;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    protected $speed;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    protected $course;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $gpsSatellites;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $gnssSatellitesUsed;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $glonass;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $CN;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $millis;


    /**
     * GPS constructor.
     * @param $log
     * @param $millis
     */
    public function __construct(Line $log =null, $millis = null)
    {
        $this->log = $log;

        $this->millis = $millis;
    }


    public function getLog(): ?Line
    {
        return $this->log;
    }

    public function getMessage()
    {
        $log = $this->getLog();
        $log->getMessage();
    }

    public function setLog(?Line $log): self
    {
        $this->log = $log;

        return $this;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(?\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function setDateTimeBytes($bytes)
    {
        $this->setDatetime(Converter::getDateTime($bytes));

        return $this;
    }


    public function getLatitude()
    {
        if (is_resource($this->latitude)) {
            $this->latitude = self::convertCoord(fread($this->latitude, 4));
        }
        return $this->latitude;
    }

    public static function convertCoord($bytes)
    {
        return Converter::convertCharsToLong($bytes) / 1000000;
    }

    public function setLatitude($latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude()
    {
        if (is_resource($this->longitude)) {
            $this->longitude = self::convertCoord(fread($this->longitude, 4));
        }
        return $this->longitude;
    }

    public function setLongitude($longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }


    public function getAltitude()
    {
        if (is_resource($this->altitude)) {
            $this->altitude = Converter::convertCharsToLong(fread($this->altitude, 2));
        }
        return $this->altitude;
    }

    public function setAltitude($altitude): self
    {
        $this->altitude = $altitude;

        return $this;
    }

    public function getSpeed()
    {
        if (is_resource($this->speed)) {
            $this->speed = ord(fread($this->speed, 1)) / 100;
        }
        return $this->speed;
    }

    public function setSpeed($speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getCourse()
    {
        if (is_resource($this->course)) {
            $this->course = Converter::convertCharsToLong(fread($this->course, 2)) / 100;
        }
        return $this->course;
    }

    public function setCourse($course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getGpsSatellites()
    {
        if (is_resource($this->gpsSatellites)) {
            $this->gpsSatellites = ord(fread($this->gpsSatellites, 1)) ;
        }
        return $this->gpsSatellites;
    }

    public function setGpsSatellites($gpsSatellites): self
    {
        $this->gpsSatellites = $gpsSatellites;

        return $this;
    }

    public function getGnssSatellitesUsed()
    {
        if (is_resource($this->gnssSatellitesUsed)) {
            $this->gnssSatellitesUsed = ord(fread($this->gnssSatellitesUsed, 1)) ;
        }
        return $this->gnssSatellitesUsed;
    }

    public function setGnssSatellitesUsed($gnssSatellitesUsed): self
    {
        $this->gnssSatellitesUsed = $gnssSatellitesUsed;

        return $this;
    }

    public function getGlonass()
    {
        if (is_resource($this->glonass)) {
            $this->glonass = ord(fread($this->glonass, 1)) ;
        }
        return $this->glonass;
    }

    public function setGlonass($glonass): self
    {
        $this->glonass = $glonass;

        return $this;
    }

    public function getCN()
    {
        if (is_resource($this->CN)) {
            $this->CN = ord(fread($this->CN, 1)) ;
        }
        return $this->CN;
    }

    public function setCN($CN): self
    {
        $this->CN = $CN;

        return $this;
    }

    public function getMillis()
    {
        if (is_resource($this->millis)) {
            $this->millis = Converter::convertCharsToLong(fread($this->millis, 4)) ;
        }
        return $this->millis;
    }

    public function setMillis($millis): self
    {
        $this->millis = $millis;

        return $this;
    }
}
