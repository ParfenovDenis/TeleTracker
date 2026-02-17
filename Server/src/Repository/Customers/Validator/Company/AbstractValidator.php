<?php
/*
 * Copyright (c) 2026.
 */

namespace App\Repository\Customers\Validator\Company;

use App\Entity\Customers\Company;
use App\Repository\Customers\Validator\Company\CompanyValidator;
use App\Repository\Validator\ValidatorInterface;

abstract class AbstractValidator implements ValidatorInterface
{
    protected $company;

    protected $validator;
    public function __construct(Company $company, CompanyValidator $validator)
    {
        $this->company = $company;
        $this->validator = $validator;
    }
}