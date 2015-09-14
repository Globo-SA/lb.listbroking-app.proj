<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use Adclick\TaskControllerBundle\Service\TaskServiceInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\BusinessLogic\StagingService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StagingContactsCleanUpCommand extends ContainerAwareCommand
{

    const MAX_RUNNING = 90;

    /**
     * @var TaskServiceInterface
     */
    private $service;

    protected function configure ()
    {
        $this->setName('listbroking:staging:cleanup')
             ->setDescription('Cleans up the invalid contacts from the StagingContact table')
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
            if ( $this->service->start($this, $input, $output, self::MAX_RUNNING) )
            {
                /** @var StagingService $s_service */
                $s_service = $this->getContainer()
                                  ->get('staging')
                ;

                $this->service->write('Sending invalid contacts to the Data Quality Profile table (DQP)');
                $s_service->moveInvalidContactsToDQP();

                $this->service->finish();
            }
            else
            {
                $this->service->write('Task is Already Running');
            }
        }
        catch ( \Exception $e )
        {
            $this->service->throwError($e);
        }
    }
} 