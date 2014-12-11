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
use ListBroking\TaskControllerBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeduplicateContactsCommand extends BaseCommand {


    protected function configure(){
        $this
            ->setName('listbroking:extraction:deduplicate')
            ->setDescription('Deduplicates contacts of a given Extraction')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        parent::execute($input, $output);

        $this->write("STARTING UPLOADS");

        // Get the ExtractionService and set the OutputInterface
        $e_service = $this->getContainer()->get('extraction');
        $e_service->setOutputInterface($output);

        $dir = $this->getContainer()->get('kernel')->getRootDir() . '/../web/';

        /** @var  ExtractionDeduplicationQueue[] $queues */
        $queues = $e_service->getEntities('extraction_deduplication_queue');
        if(count($queues) > 0){

            // Extractions to Deduplicate
            $extractions = array();
            $this->createProgress('STARTING QUEUE PROCESSING', count($queues));
            foreach ($queues as $queue)
            {
                $this->advanceProgress("Processing Queue ID: {$queue->getId()}");

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
            $this->finishProgress();

            $this->createProgress('STARTING DEDUPLICATIONS', count($extractions));
            foreach ($extractions as $id =>$extraction)
            {
                $this->advanceProgress("Deduplicating Extraction ID: {$id}");
                $e_service->deduplicateExtraction($extraction);
            }
            $this->finishProgress();
        }else{
            $this->write('Nothing to process');
        }

        $this->write('END');
    }
}