<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use Adclick\TaskControllerBundle\Service\TaskServiceInterface;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Service\BusinessLogic\StagingService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateInitialLocksCommand extends ContainerAwareCommand
{

    const MAX_RUNNING = 1;

    /**
     * @var TaskServiceInterface
     */
    private $service;

    protected function configure ()
    {
        $this->setName('listbroking:staging:update_initial_locks')
             ->setDescription('Updates leads that are ready to be used in extractions')
             ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max Leads to validate per task iteration', 100)
        ;
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        /** @var TaskServiceInterface $service */
        $this->service = $this->getContainer()
                              ->get('task')
        ;
        try
        {
            if ( ! $this->service->start($this, $input, $output, self::MAX_RUNNING) )
            {
                $this->service->write('Task is Already Running');

                return;
            }

            $limit = $input->getOption('limit');

            /** @var StagingService $s_service */
            $s_service = $this->getContainer()
                              ->get('staging')
            ;

            $leads = $s_service->findLeadsWithExpiredInitialLock($limit);
            if ( ! $leads )
            {
                $this->service->write('No leads to process');
                $this->service->finish();

                return;
            }

            $this->service->write(sprintf('Updating %s Lead(s) with expired Initial Lock (TYPE_INITIAL_LOCK)', count($leads)));

            /** @var Lead $lead */
            foreach ( $leads as $lead )
            {
                $this->service->write("Updating Lead: {$lead->getId()}");
                $lead->setIsReadyToUse(1);
            }
            $s_service->flushAll();

            $this->service->finish();
        }
        catch ( \Exception $e )
        {
            $this->service->throwError($e);
            $this->service->write($e->getTraceAsString());
        }
    }
} 