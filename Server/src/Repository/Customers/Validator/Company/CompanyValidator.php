<?php
/*
 * Copyright (c) 2026.
 */

namespace App\Repository\Customers\Validator\Company;

use App\Entity\Customers\Company;
use App\Repository\Customers\Validator\Company\Exception\ValidationException;


class CompanyValidator
{
    protected $errors = [];

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function validate(): bool
    {
        $this->checkCompanies();
        $this->checkDrivers();
        $this->checkTrucks();
        if (count($this->errors) > 0)
            throw new ValidationException($this->errors);

        return true;
    }

    private function checkTrucks()
    {
        $this->validator = new TrucksValidator($this->company, $this);
        $this->validator->validate();
    }

    private function checkDrivers()
    {
        $this->validator = new DriversValidator($this->company, $this);
        $this->validator->validate();
    }

    private function checkCompanies()
    {
        $this->validator = new CompaniesValidator($this->company, $this);
        $this->validator->validate();
    }

    /**
     * @param array $errors
     */
    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }
}