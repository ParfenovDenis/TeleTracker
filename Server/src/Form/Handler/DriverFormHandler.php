<?php
/*
 * Copyright (c) 2022.
 */

namespace App\Form\Handler;

use App\Entity\Customers\Driver;
use App\Manager\DriverManager;
use Symfony\Component\Form\Form;


class DriverFormHandler
{
    /**
     * @var DriverManager
     */
    private $driverManager;

    /**
     * DriverFormHandler constructor.
     * @param DriverManager $driverManager
     */
    public function __construct(DriverManager $driverManager)
    {
        $this->driverManager = $driverManager;
    }

    /**
     * @param Driver $driver
     * @param Form   $form
     *
     * @return Driver
     */
    public function processEditForm(Driver $driver): Driver
    {
        $this->driverManager->save($driver);

        return $driver;
    }
}
