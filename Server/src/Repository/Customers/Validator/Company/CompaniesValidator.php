<?php
/*
 * Copyright (c) 2026.
 */

namespace App\Repository\Customers\Validator\Company;

use App\Repository\Customers\Validator\Company\AbstractValidator;
use App\Repository\Validator\ValidatorInterface;

class CompaniesValidator extends AbstractValidator implements ValidatorInterface

{
    public function validate(): bool
    {
        if ($this->company->getChildren())
        {
            $this->validator->addError('company_can_not_delete');
            return false;
        }

        return true;
    }
}