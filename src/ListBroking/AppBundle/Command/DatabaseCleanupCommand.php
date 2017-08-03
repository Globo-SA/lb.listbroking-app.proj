<?php
/**
 * @author     Diogo Basto <diogo.basto@adclick.pt>
 * @copyright  2017 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use ListBroking\AppBundle\Service\BusinessLogic\ExtractionService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseCleanupCommand extends ContainerAwareCommand
{
    /**
     * @var ExtractionService
     */
    protected $extractionService;

    /**
     * @var array
     */
    protected $expiration;

    /**
     * @inheritdoc
     */
    protected function configure ()
    {
        $this->setName('listbroking:database:cleanup')
             ->setDescription('Task to cleanup old data')
             ->addOption('table', null, InputOption::VALUE_OPTIONAL, 'Table to cleanup. Default: all', 'all')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $selectedTable = strtolower($input->getOption('table'));
        $container = $this->getContainer();
        $this->extractionService = $container->get('extraction');
        $this->expiration = $this->extractionService->findConfig('cleanup_expire');
        switch ($selectedTable)
        {
            case 'all':
                $this->cleanTableExceptionLog();
                $this->cleanTableExtraction();
                $this->cleanTableLock();
                $this->cleanTableStagingContactDQP();
                $this->cleanTableStagingContactProcessed();
                break;
            case 'exception_log':
                $this->cleanTableExceptionLog();
                break;
            case 'extraction':
                $this->cleanTableExtraction();
                break;
            case 'lb_lock':
                $this->cleanTableLock();
                break;
            case 'staging_contact_dqp':
                $this->cleanTableStagingContactDQP();
                break;
            case 'staging_contact_processed':
                $this->cleanTableStagingContactProcessed();
                break;
            case 'task':
                break;
            default:
                $output->writeln("Table is not valid for clean-up.");
                $output->writeln("Accepted values: ['all' (default), 'exception_log', 'extraction', 'lb_lock', 'staging_contact_dqp', 'staging_contact_processed', 'task']");
        }
    }

    private function cleanTableExceptionLog()
    {
        $minDate = new \DateTime();
        $minDate->sub(new \DateInterval('P' . $this->expiration['exception'] . 'M'));
        $minDateStr = $minDate->format('Y-m-d');
        $this->extractionService->logInfo("Exception_log: Locating records older than {$minDateStr} for cleaning.");

        $repository = $this->extractionService->entity_manager->getRepository('ListBrokingExceptionHandlerBundle:ExceptionLog');
        $id = $repository->locateIdOnDate($minDate);
        if (!$id)
        {
            $this->extractionService->logInfo("Exception_log: Didn't find any exception to cleanup.");
            return;
        }
        //Id is after date, we want to erase before.
        $id -= 1;
        $this->extractionService->logInfo("Exception_log: Cleaning table until #{ $id }" );
        $repository->cleanUp($id);
    }

    private function cleanTableLock()
    {
        $minDate = new \DateTime();
        $minDate->sub(new \DateInterval('P' . $this->expiration['lb_lock'] . 'M'));
        $minDateStr = $minDate->format('Y-m-d');
        $this->extractionService->logInfo("Lock: Locating records older than {$minDateStr} for cleaning.");

        $repository = $this->extractionService->entity_manager->getRepository('ListBrokingAppBundle:Lock');
        $id = $repository->locateIdOnDate($minDate);
        if (!$id)
        {
            $this->extractionService->logInfo("Lock: Didn't find any locks to cleanup.");
            return;
        }
        //Id is after date, we want to erase before.
        $id -= 1;
        $this->extractionService->logInfo("Lock: Cleaning table until #{ $id }" );
        $repository->cleanUp($id);
    }

    private function cleanTableStagingContactDQP()
    {
        $minDate = new \DateTime();
        $minDate->sub(new \DateInterval('P' . $this->expiration['staging_contact_dqp'] . 'M'));
        $minDateStr = $minDate->format('Y-m-d');
        $this->extractionService->logInfo("StagingContactDQP: Locating records older than {$minDateStr} for cleaning.");

        $repository = $this->extractionService->entity_manager->getRepository('ListBrokingAppBundle:StagingContactDQP');
        $id = $repository->locateIdOnDate($minDate);
        if (!$id)
        {
            $this->extractionService->logInfo("StagingContactDQP: Didn't find any contacts to cleanup.");
            return;
        }
        //Id is after date, we want to erase before.
        $id -= 1;
        $this->extractionService->logInfo("StagingContactDQP: Cleaning table until #{ $id }" );
        $repository->cleanUp($id);
    }

    private function cleanTableStagingContactProcessed()
    {
        $minDate = new \DateTime();
        $minDate->sub(new \DateInterval('P' . $this->expiration['staging_contact_processed'] . 'M'));
        $minDateStr = $minDate->format('Y-m-d');
        $this->extractionService->logInfo("StagingContactProcessed: Locating records older than {$minDateStr} for cleaning.");

        $repository = $this->extractionService->entity_manager->getRepository('ListBrokingAppBundle:StagingContactProcessed');
        $id = $repository->locateIdOnDate($minDate);
        if (!$id)
        {
            $this->extractionService->logInfo("StagingContactProcessed: Didn't find any contacts to cleanup.");
            return;
        }
        //Id is after date, we want to erase before.
        $id -= 1;
        $this->extractionService->logInfo("StagingContactProcessed: Cleaning table until #{ $id }" );
        $repository->cleanUp($id);
    }

    /**
     * This function clears data associated to the extraction,
     * but not the extraction itself, so it can be cloned if
     * the need comes up.
     */
    private function cleanTableExtraction()
    {
        $minDate = new \DateTime();
        $minDate->sub(new \DateInterval('P' . $this->expiration['extraction'] . 'M'));
        $minDateStr = $minDate->format('Y-m-d');
        $this->extractionService->logInfo("Extraction: Locating records older than {$minDateStr} for cleaning.");

        $repository = $this->extractionService->entity_manager->getRepository('ListBrokingAppBundle:Extraction');

        //this table is very small, so if we query it by an unindexed field it's not too slow.
        $extraction = $repository->findLastExtractionBeforeDate($minDateStr);
        if (!$extraction)
        {
            $this->extractionService->logInfo("Extraction: Didn't find any extraction to cleanup.");
            return ;
        }
        $extractionId = $extraction->getId();
        $this->extractionService->logInfo("Extraction: Cleaning tables until #{ $extractionId }" );

        $eLogRepository = $this->extractionService->entity_manager->getRepository('ListBrokingAppBundle:ExtractionLog');
        $eLogRepository->cleanUp($extractionId);

        $eContactRepository = $this->extractionService->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact');
        $eContactRepository->cleanUp($extractionId);

        $eDeduplicationRepository = $this->extractionService->entity_manager->getRepository('ListBrokingAppBundle:ExtractionDeduplication');
        $eDeduplicationRepository->cleanUp($extractionId);
        $this->extractionService->logInfo("Extraction: successfully wiped out old extractions.");
    }

}
