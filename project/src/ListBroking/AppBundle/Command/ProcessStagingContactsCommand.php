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
use ListBroking\AppBundle\Service\BusinessLogic\StagingServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessStagingContactsCommand extends ContainerAwareCommand
{
    const MAX_RUNNING = 200;

    const MAX_WAITING_TIME = 30;

    const LOCK_MODULE = 'find_and_lock_contacts';

    /**
     * @var TaskServiceInterface
     */
    private $taskService;

    /**
     * @var StagingServiceInterface
     */
    private $stagingContactService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('listbroking:staging:process')
             ->setDescription('Processes StagingContacts and send them to the prod env')
             ->addOption(
                 'limit',
                 'l',
                 InputOption::VALUE_OPTIONAL,
                 'Max contacts to validate per task iteration',
                 100
             );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container                   = $this->getContainer();
        $this->logger                = $container->get('logger');
        $this->taskService           = $container->get('task');
        $this->stagingContactService = $container->get('app.service.staging');

        $this->logger->info('Start execution');

        try {
            if (!$this->taskService->start($this, $input, $output, self::MAX_RUNNING)) {
                $this->logger->info('Cant continue.. MAX_RUNNING');

                return;
            }

            $this->logger->info('Find staging contacts to process');

            $limit           = $input->getOption('limit');
            $stagingContacts = $this->findContactsToProcess($limit);

            if (!$stagingContacts) {
                $this->logger->info('No staging contacts to process');
                $this->taskService->finish();

                return;
            }

            // Iterate staging contacts
            $this->logger->info('Start staging contacts process');
            foreach ($stagingContacts as $stagingContact) {

                $this->logger->debug('Process staging contact id:' . $stagingContact->getId());
                if ($stagingContact->getUpdate()) {
                    $this->stagingContactService->loadUpdatedContact($stagingContact);

                    $this->logger->debug(sprintf('Staging Id %s as been processed', $stagingContact->getId()));

                    continue;
                }

                $this->stagingContactService->validateStagingContact($stagingContact);
                $this->loadValidContact($stagingContact);
                $this->logger->debug(sprintf('Staging Id %s as been processed', $stagingContact->getId()));
            }

            $this->logger->info('End of staging contacts process');

            $this->taskService->finish();
        } catch (\Exception $e) {
            $this->taskService->throwError($e);
            $this->taskService->write($e->getTraceAsString());
        }
    }

    /**
     * Finds StagingContacts to process, waits if another task is already finding
     * contacts to process
     *
     * @param $limit
     *
     * @return \ListBroking\AppBundle\Entity\StagingContact[]
     */
    private function findContactsToProcess($limit)
    {
        $start = time();
        $this->logger->info('Trying to lock module');

        while ($this->stagingContactService->isExecutionLocked(self::LOCK_MODULE)) {
            $now = time();

            if (($now - $start) > self::MAX_WAITING_TIME) {
                $this->logger->error('Could not get a lock');

                return null;
            }
        }

        $this->logger->info('Selecting contacts. Obtain LOCK');
        $this->stagingContactService->lockExecution(self::LOCK_MODULE);

        $stagingContacts = $this->stagingContactService->findAndLockContactsToValidate($limit);

        $this->logger->info('Contacts found. Release LOCK');
        $this->stagingContactService->releaseExecution(self::LOCK_MODULE);

        return $stagingContacts;
    }

    /**
     * Loads valid StagingContacts
     *
     * @param StagingContact $stagingContact
     */
    private function loadValidContact(StagingContact $stagingContact)
    {
        // Load validated contact
        if ($stagingContact->getValid() && $stagingContact->getProcessed()) {
            $this->taskService->write("Loading StagingContact: {$stagingContact->getId()}");
            $this->stagingContactService->loadValidatedContact($stagingContact);
        }
    }
}
