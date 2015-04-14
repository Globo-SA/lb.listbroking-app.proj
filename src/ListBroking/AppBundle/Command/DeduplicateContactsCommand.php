<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use ListBroking\AppBundle\Service\Helper\AppService;
use ListBroking\TaskControllerBundle\Entity\Queue;
use ListBroking\TaskControllerBundle\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeduplicateContactsCommand extends ContainerAwareCommand {

    const MAX_RUNNING = 1;

    /**
     * @var TaskService
     */
    private $service;

    protected function configure(){
        $this
            ->setName('listbroking:extraction:deduplicate')
            ->setDescription('Deduplicates contacts of a given Extraction waiting on the Queue system')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $this->service = $this->getContainer()->get('task');
        try{
            if($this->service->start($this, $input, $output, self::MAX_RUNNING)){

                $e_service = $this->getContainer()->get('extraction');
                $dir = $this->getContainer()->get('kernel')->getRootDir() . '/../web/';

                /** @var  Queue[] $queues */
                $queues = $this->service->findQueuesByType(AppService::DEDUPLICATION_QUEUE_TYPE);
                if(count($queues) > 0){

                    $extractions = array();

                    // Iterate deduplication queues
                    $this->service->createProgressBar('STARTING QUEUE PROCESSING', count($queues));
                    foreach ($queues as $queue)
                    {
                        $this->service->advanceProgressBar("Processing Queue ID: {$queue->getId()}");

                        // Persist deduplications to the DB
                        $filename = $dir . $queue->getValue2();
                        $extraction = $e_service->getEntity('extraction', $queue->getValue1(), true, true);
                        $e_service->uploadDeduplicationsByFile($extraction, $filename, $queue->getValue3(), true);

                        // Add Extraction to the duplication process
                        $extractions[$extraction->getId()] = $extraction;
                    }
                    $this->service->finishProgressBar();

                    // Deduplicate contacts for the necessary extractions
                    $this->service->createProgressBar('STARTING DEDUPLICATIONS', count($extractions));
                    foreach ($extractions as $id =>$extraction)
                    {
                        $this->service->advanceProgressBar("Deduplicating Extraction ID: {$id}");
                        $e_service->executeFilterEngine($extraction);
                        $e_service->updateEntity('extraction', $extraction);
                    }
                    $this->service->finishProgressBar();

                    // Clears everything if all went OK
                    $this->service->createProgressBar('REMOVING PROCESSED QUEUES', count($extractions));
                    foreach ($queues as $queue)
                    {
                        $this->service->advanceProgressBar("Removing Queue ID: {$queue->getId()}");

                        // Remove file and Queue
                        $e_service->removeEntity('queue', $queue);
                        $filename = $dir . $queue->getValue2();
                        unlink($filename);
                    }
                    $this->service->finishProgressBar();

                }else{
                    $this->service->write('Nothing to process');
                }

                $this->service->finish();
            }else{
                $this->service->write('Task is Already Running');
            }
        }catch (\Exception $e){
            $this->service->throwError($e);
        }
    }
}