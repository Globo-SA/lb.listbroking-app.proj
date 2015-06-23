<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use ListBroking\AppBundle\Service\Helper\AppService;
use ListBroking\TaskControllerBundle\Entity\Queue;
use ListBroking\TaskControllerBundle\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportStagingContactsCommand extends ContainerAwareCommand
{

    const MAX_RUNNING = 1;

    /**
     * @var TaskService
     */
    private $service;

    protected function configure ()
    {
        $this->setName('listbroking:staging:import')
             ->setDescription('Imports staging contact files waiting on the Queue system')
        ;
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {

        /** @var TaskService $service */
        $this->service = $this->getContainer()
                              ->get('task')
        ;
        $dir =
            $this->getContainer()
                 ->get('kernel')
                 ->getRootDir() .
            '/../web/';

        try
        {
            if ( $this->service->start($this, $input, $output, self::MAX_RUNNING) )
            {
                $s_service = $this->getContainer()
                                  ->get('staging')
                ;

                /** @var  Queue[] $queues */
                $queues = $this->service->findQueuesByType(AppService::CONTACT_IMPORT_QUEUE_TYPE);
                if ( count($queues) > 0 )
                {

                    // Iterate deduplication queues
                    $this->service->createProgressBar('STARTING QUEUE PROCESSING', count($queues));
                    foreach ( $queues as $queue )
                    {
                        $filename = $dir . $queue->getValue1();
                        $this->service->advanceProgressBar("Importing file: {$filename}");
                        $s_service->importStagingContacts($filename);

                        // Remove file and Queue
                        $s_service->removeEntity('queue', $queue);
                        unlink($filename);
                    }
                    $this->service->finishProgressBar();
                }
                else
                {
                    $this->service->write('Nothing to process');
                }

                $s_service->syncContactsWithOppositionLists();
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