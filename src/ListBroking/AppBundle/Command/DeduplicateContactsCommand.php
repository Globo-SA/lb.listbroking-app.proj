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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeduplicateContactsCommand extends ContainerAwareCommand {


    protected function configure(){
        $this
            ->setName('listbroking:extraction:deduplicate')
            ->setDescription('Deduplicates contacts of a given Extraction')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $output->writeln("<info>DeduplicateContactsCommand:</info> <comment>START</comment>");

        // Get the ExtractionService and set the OutputInterface
        $e_service = $this->getContainer()->get('extraction');
        $e_service->setOutputInterface($output);

        $dir = $this->getContainer()->get('kernel')->getRootDir() . '/../web/';

        /** @var  ExtractionDeduplicationQueue[] $queues */
        $queues = $e_service->getEntities('extraction_deduplication_queue');
        if(count($queues) > 0){
            $output->writeln('\n');
            $progress = new ProgressBar($output, count($queues));
            $progress->setBarCharacter('<info>=</info>');
            $progress->setFormat("%current%/%max% [<comment>%bar%</comment>] %percent%%\n<fg=white;bg=blue> %message% </>");
            foreach ($queues as $queue)
            {
                $progress->setMessage("Processing Queue ID: {$queue->getId()}");
                $progress->advance();

                // Persist deduplications to the DB
                $filename = $dir . $queue->getFilePath();
                $e_service->persistDeduplications($filename ,$queue->getExtraction(), $queue->getField(), true);

                // Remove file and Queue
                unlink($filename);
                $e_service->removeEntity('extraction_deduplication_queue', $queue);
            }
            $progress->setMessage("Processing DONE");
            $progress->finish();
        }else{
            $output->writeln("<info>DeduplicateContactsCommand:</info> <error>Nothing to process</error>");
        }

        $output->writeln("<info>DeduplicateContactsCommand:</info> <comment>END</comment>");
    }
}