<?php
/*
 * Copyright (c) 2024.
 */

namespace App\Model\Handler;


use App\Doctrine\Persister\PersisterInterface;
use App\Entity\HTTP\Request;
use App\Model\AbstractObjectBuilder;
use App\Model\ObjectBuilder;
use App\Repository\HTTP\RequestRepository;
use Doctrine\ORM\ORMInvalidArgumentException;


/**
 * Handler of Requests
 */
class RequestHandler
{

    /**
     * @var PersisterInterface
     */
    private $persister;

    private $successInserts = 0;

    /**
     * @param PersisterInterface $persister
     */
    public function __construct(PersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function handle(Request $request): bool
    {
        $request->setIsProcessed(true);
        $this->persister->flushRequest($request);
        $requestContent = $request->getContent();
        $class = ObjectBuilder::class.'V'.$request->getVersion();
        $persisted = false;


        if ($request->getVersion() > 1 && class_exists($class)) {
            /**
             * @var AbstractObjectBuilder $ob
             */
            $ob = new $class($requestContent);
            $logLines = $ob->setRequest($request)->build()->getRequest()->getLogs();

            foreach ($logLines as $logLine) {
                $result = $this->persister
                    ->persistLog($logLine)
                    ;
                if ($result) {
                    $this->successInserts++;
                }
                $persisted = true;
            }
            $this->persister->flush();
        }

        if (!$persisted) {
            throw new \RuntimeException('No Loglines are written to the db ');
        }

        return true;
    }

    public function getSuccessInserts():int {
        return $this->successInserts;
    }
}
