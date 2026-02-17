<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Doctrine\Persister;

use App\Entity\CanBus\Log\Line;
use App\Entity\CanBus\Message;
use App\Entity\HTTP\Request;
use App\Entity\Module\GPS;
use App\Entity\Module\Modem;

interface PersisterInterface
{
    public function persistLog(Line $line): self;

    public function flushRequest(Request $request):void;
    public function flush(): bool;
}