<?php
/*
 * Copyright (c) 2023.
 */

namespace App\Form\Handler;


use App\Manager\DriverManager;
use App\Manager\DriverTruckManager;
use App\Model\ConflictDriverTruckManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DriverTruckFormHandler
{
    /**
     * @var DriverTruckManager
     */
    private $driverTruckManager;

    private $conflictDriverTruckManager;

    private $translator;

    /**
     * DriverFormHandler constructor.
     * @param DriverTruckManager $driverTruckManager
     */
    public function __construct(DriverManager $driverTruckManager, ConflictDriverTruckManager $conflictDriverTruckManager, TranslatorInterface $translator)
    {
        $this->driverTruckManager = $driverTruckManager;
        $this->conflictDriverTruckManager = $conflictDriverTruckManager;
        $this->translator = $translator;
    }

    /**
     * @param Form $form
     *
     *
     * @return bool
     *
     * @throws ConflictDriverTruckException
     */
    public function processForm(FormInterface $form): bool
    {
        $driverTruck = $form->getData();
        $confirm = (int) $form->get('confirm')->getData();
        if ($this->conflictDriverTruckManager->exist($driverTruck)) {
            if ($confirm) {
                $this->conflictDriverTruckManager->resolve();
            } else {
                throw new ConflictDriverTruckException($this->conflictDriverTruckManager, $this->translator);
            }
        } else {
            $this->driverTruckManager->save($driverTruck);
        }

        return true;
    }
}
