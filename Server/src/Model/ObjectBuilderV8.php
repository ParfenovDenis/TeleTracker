<?php
/**
 * Copyright (c) 2021.
 */

namespace App\Model;

class ObjectBuilderV8 extends ObjectBuilderV6
{
    const VERSION = '8';

    public function build0(): AbstractObjectBuilder
    {
        $this->bytes = substr($this->bytes, 3);

        return parent::build();
    }
}
