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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessStagingContactsCommand extends ContainerAwareCommand
{
    const MAX_RUNNING      = 10;

    const MAX_WAITING_TIME = 30;

    const LOCK_MODULE      = 'find_and_lock_contacts';

    /**
     * @var TaskServiceInterface
     */
    private $service;

    /**
     * @var StagingServiceInterface
     */
    private $stagingService;

    /**
     * {@inheritdoc}
     */
    protected function configure ()
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
        $container            = $this->getContainer();
        $this->service        = $container->get('task');
        $this->stagingService = $container->get('app.service.staging');

        try {
            if (! $this->service->start($this, $input, $output, self::MAX_RUNNING)) {
                $this->service->write('Task is Already Running');

                return;
            }

            $limit    = $input->getOption('limit');
            $contacts = $this->findContactsToProcess($limit);

            if (! $contacts) {
                $this->service->write('No contacts to process');
                $this->service->finish();

                return;
            }

            // Iterate staging contacts
            $this->service->write('Stating contact Validation');

            foreach ($contacts as $stagingContact) {
                if ($stagingContact->getUpdate()) {
                    $this->service->write(
                        sprintf(
                            'Loading StagingContact: id: %s, contact_id: %s',
                            $stagingContact->getId(),
                            $stagingContact->getContactId()
                        )
                    );
                    $this->stagingService->loadUpdatedContact($stagingContact);

                    continue;
                }

                $this->stagingService->validateStagingContact($stagingContact);
                $this->loadValidContact($stagingContact);
            }

            $this->stagingService->syncContactsWithOppositionLists();

            $this->service->finish();
        } catch (\Exception $e) {
            $this->service->throwError($e);
            $this->service->write($e->getTraceAsString());
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
    private function findContactsToProcess ($limit)
    {
        $start = time();
        $this->service->write('Trying to lock module');

        while ($this->stagingService->isExecutionLocked(self::LOCK_MODULE)) {
            $now = time();

            if (($now - $start) > self::MAX_WAITING_TIME) {
                $this->service->write('Could not get a lock');

                return null;
            }

            sleep(1);
        }

        $this->service->write('Selecting contacts');
        $this->stagingService->lockExecution(self::LOCK_MODULE);

        $contacts = $this->stagingService->findAndLockContactsToValidate($limit);
        $this->stagingService->releaseExecution(self::LOCK_MODULE);

        return $contacts;
    }

    /**
     * Loads valid StagingContacts
     *
     * @param StagingContact $staging_contact
     */
    private function loadValidContact (StagingContact $staging_contact)
    {
        // Load validated contact
        if ( $staging_contact->getValid() && $staging_contact->getProcessed() ) {
            $this->service->write("Loading StagingContact: {$staging_contact->getId()}");
            $this->stagingService->loadValidatedContact($staging_contact);
        }
    }
}
