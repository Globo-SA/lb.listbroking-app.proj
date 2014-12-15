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


use ListBroking\AppBundle\Entity\ExtractionDeduplicationQueue;
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
            ->setDescription('Deduplicates contacts of a given Extraction')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->service = $this->getContainer()->get('task');
        try{
            if($this->service->start($this, $input, $output, DeduplicateContactsCommand::MAX_RUNNING)){

                // Get the ExtractionService and set the OutputInterface
                $e_service = $this->getContainer()->get('extraction');
                $e_service->setOutputInterface($output);

                $dir = $this->getContainer()->get('kernel')->getRootDir() . '/../web/';

                /** @var  ExtractionDeduplicationQueue[] $queues */
                $queues = $e_service->getEntities('extraction_deduplication_queue');
                if(count($queues) > 0){

                    // Extractions to Deduplicate
                    $extractions = array();
                    $this->service->createProgressBar('STARTING QUEUE PROCESSING', count($queues));
                    foreach ($queues as $queue)
                    {
                        $this->service->advanceProgressBar("Processing Queue ID: {$queue->getId()}");

                        // Persist deduplications to the DB
                        $filename = $dir . $queue->getFilePath();
                        $e_service->uploadDeduplicationsByFile($filename ,$queue->getExtraction(), $queue->getField(), true);

                        // Remove file and Queue
                        unlink($filename);
                        $e_service->removeEntity('extraction_deduplication_queue', $queue);

                        // Add Extraction to the duplication Queue
                        $extraction = $queue->getExtraction();
                        $extractions[$extraction->getId()] = $extraction;

                    }
                    $this->service->finishProgressBar();

                    $this->service->createProgressBar('STARTING DEDUPLICATIONS', count($extractions));
                    foreach ($extractions as $id =>$extraction)
                    {
                        $this->service->advanceProgressBar("Deduplicating Extraction ID: {$id}");
                        $e_service->deduplicateExtraction($extraction);
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