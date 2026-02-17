<?php
/**
 * @license AVT
 */
namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Report\EventRepository")
 * @ORM\Table(name="event")
 */
class Event
{
    const LAST_STATE_ID = 1;
    const PARKING_ID = 2;
    const REFUELING_ID = 3;
    const VIOLATION_ID = 4;
    const VISIT_LOCATION_ID = 5;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Event
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }


}
