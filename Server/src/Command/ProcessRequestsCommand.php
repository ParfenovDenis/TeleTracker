<?php
/**
 * @license AVT
 */

namespace App\Command;

use App\Model\Handler\RequestHandler;
use App\Repository\HTTP\RequestRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @class ProcessRequestsCommand
 */
class ProcessRequestsCommand extends Command
{
    protected static $defaultName = 'process-requests';

    private $requestRepository;

    private $handler;

    /**
     * @param RequestRepository $requestRepository
     * @param RequestHandler    $handler
     */
    public function __construct(RequestRepository $requestRepository, RequestHandler $handler)
    {
        $this->requestRepository = $requestRepository;
        $this->handler = $handler;
        parent::__construct();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output):int
    {
        $io = new SymfonyStyle($input, $output);
        $r = 0;
        $totalTime = microtime(true);
        try {
            $requests = $this->requestRepository->getRawRequests();
            $total = count($requests);
            foreach ($requests as $request) {
                $r++;
                $tRequest = microtime(true);
                try {
                    if ($this->handler->handle($request)) {
                        $io->block('time: '.(microtime(true) - $tRequest));
                        $io->success('Request_id = '.$request->getId().' '.$r.'/'.$total);
                    }
                } catch (\Throwable $e) {
                    $io->error('Exception: '.get_class($e).' Message: '.$e->getMessage().' FILE: '.$e->getFile().' LINE: '.$e->getLine());
                    $io->error('Request_id = '.$request->getId());
                    gc_collect_cycles();
                }
            }
        } catch (\Throwable $exception) {
            $io->error(
                'Message: '.$exception->getMessage().
                ' File: '.$exception->getFile().
                ' Line: '.$exception->getLine().
                ' Trace: '.$exception->getTraceAsString()
            );
        }
        $io->block('Total time: '.(microtime(true) - $totalTime));

        return 0;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Processing raw requests teletracker');
    }
}
