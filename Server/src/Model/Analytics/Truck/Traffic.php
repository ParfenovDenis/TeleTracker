<?php
/**
 * Copyright (c) 2021.
 */

namespace App\Model\Analytics\Truck;


class Traffic
{
    const STATUS_MOVE = 'traffic_moving';
    const STATUS_STOP = 'traffic_parking';
    const DESCRIPTION_FORMAT_MOVE = 'traffic_status_move';
    const DESCRIPTION_FORMAT_STOP = 'traffic_status_stop';
    private $status;
    private $description_format;
    private $description;

    /**
     * Traffic constructor.
     */
    public function __construct(int $speed, \DateInterval  $time = null)
    {
        if ($speed > 0) {
            $this->status = self::STATUS_MOVE;
            $this->description_format = self::DESCRIPTION_FORMAT_MOVE;
            $this->description = $speed;
        } else {
            $this->status = self::STATUS_STOP;
            $this->description_format = self::DESCRIPTION_FORMAT_STOP;
            $this->description = $time?$time->format("%H:%I"):'';
        }
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getDescriptionFormat(): string
    {
        return $this->description_format;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }


}