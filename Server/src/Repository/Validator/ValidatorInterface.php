<?php
/*
 * Copyright (c) 2026.
 */

namespace App\Repository\Validator;

use App\Entity\Customers\Company;

interface ValidatorInterface
{

    public function validate():bool;
}