<?php
/*
 * Copyright (c) 2023.
 */

namespace App\Form\Handler;

use App\Model\ConflictDriverTruckManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConflictDriverTruckException extends \Exception
{

    private $title;

    /**
     * ConflictDriverTruckException constructor.
     */
    public function __construct(ConflictDriverTruckManager $conflictDriverTruckManager, TranslatorInterface $translator)
    {
        $conflictDriverTrucksStr = '<ul>';
        foreach ($conflictDriverTruckManager->getDriverTrucks() as $conflictDriverTruck) {
            $conflictDriverTrucksStr .= '<li>' . $conflictDriverTruck->getDriver()->getFIO() . ' (' . $conflictDriverTruck->getDateTimeFrom()->format("d.m.Y H:i") . ' - '
                . ($conflictDriverTruck->getDateTimeTo() ? $conflictDriverTruck->getDateTimeTo()->format("d.m.Y H:i") : $translator->trans('driver_truck_current_time')) . ')';
        }
        $conflictDriverTrucksStr .= '</ul>';
        $driverTruck = $conflictDriverTruckManager->getDriverTruck();
        $message = $translator->trans(
            'driver_truck_conflict_body',
            [
                '%1' => $conflictDriverTrucksStr,
                '%2' => $driverTruck->getTruck()->getTitle(),
                '%3' => $driverTruck->getDriver()->getFIO(),
                '%4' => $driverTruck->getDateTimeFrom()->format("d.m.Y H:i") . ' - ' .
                    ($driverTruck->getDateTimeTo() ? $driverTruck->getDateTimeTo()->format("d.m.Y H:i") : $translator->trans('driver_truck_current_time')),
            ]
        );

        $this->title = $translator->trans('driver_truck_conflict_title');

        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
