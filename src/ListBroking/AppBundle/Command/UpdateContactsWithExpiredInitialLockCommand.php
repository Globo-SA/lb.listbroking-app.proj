<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use Adclick\TaskControllerBundle\Service\TaskServiceInterface;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Service\BusinessLogic\StagingService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateContactsWithExpiredInitialLockCommand extends ContainerAwareCommand
{

    const MAX_RUNNING = 100;

    /**
     * @var TaskServiceInterface
     */
    private $service;

    protected function configure ()
    {
        $this->setName('listbroking:contact:update_initial_lock')
             ->setDescription('Updates contacts that are ready to be used in extractions')
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
            $s_service = $this->getContainer()
                              ->get('staging')
            ;

            $contacts = $s_service->findContactsWithExpiredInitialLock($limit);
            if ( ! $contacts )
            {
                $this->service->write('No contacts to process');
                $this->service->finish();

                return;
            }

            $this->service->write(sprintf('Updating %s Contact(s) with expired Initial Lock (TYPE_INITIAL_LOCK)', count($contacts)));

            /** @var Contact $contact */
            foreach ( $contacts as $contact )
            {
                $this->service->write("Updating Contact: {$contact->getId()}");
                $contact->setIsReadyToUse(1);
            }
            $s_service->flushAll();

            $this->service->finish();
        }
        catch ( \Exception $e )
        {
            $this->service->throwError($e);
            $this->service->write($e->getTraceAsString());
        }
    }
} 