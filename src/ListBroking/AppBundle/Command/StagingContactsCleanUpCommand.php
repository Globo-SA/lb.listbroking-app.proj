<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use Adclick\TaskControllerBundle\Service\TaskServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\StagingService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StagingContactsCleanUpCommand extends ContainerAwareCommand
{

    const MAX_RUNNING = 1;

    /**
     * @var TaskServiceInterface
     */
    private $service;

    protected function configure ()
    {
        $this->setName('listbroking:staging:cleanup')
             ->setDescription('Cleans up the invalid contacts from the StagingContact table')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max contacts to validate per task iteration', 100)
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
            $s_service = $this->getContainer()->get('app.service.staging');

            $this->service->write('Sending invalid contacts to the Data Quality Profile table (DQP)');
            $s_service->moveInvalidContactsToDQP($limit);

            $this->service->finish();
        }
        catch ( \Exception $e )
        {
            $this->service->throwError($e);
            $this->service->write($e->getTraceAsString());
        }
    }
} 