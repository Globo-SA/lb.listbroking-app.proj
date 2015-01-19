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


use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\TaskControllerBundle\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessStagingContactsCommand extends ContainerAwareCommand{

    const MAX_RUNNING = 1;

    /**
     * @var TaskService
     */
    private $service;

    protected function configure(){
        $this
            ->setName('listbroking:staging:process')
            ->setDescription('Processes StagingContacts and send them to the prod env')
            ->addArgument('limit', InputArgument::OPTIONAL, 'Max contacts to validate per task iteration', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        /** @var TaskService $service */
        $this->service = $this->getContainer()->get('task');
        try{
            if($this->service->start($this, $input, $output, self::MAX_RUNNING)){

                $s_service = $this->getContainer()->get('staging');

                $limit = $input->getArgument('limit');

                /** @var  StagingContact[] $contacts */
                $contacts = $s_service->findContactsToValidate($limit);

                // Iterate staging contacts
                $this->service->createProgressBar('STARTING CONTACT VALIDATION', count($contacts));
                foreach ($contacts as $contact)
                {
                    $this->service->advanceProgressBar("Validating StagingContact: {$contact->getId()}");

                    // Validate
                    $s_service->validateStagingContact($contact);

                    // Load validated contact
                    if($contact->getValid() && $contact->getProcessed()){
                        $this->service->setProgressBarMessage("Loading StagingContact: {$contact->getId()}");

                        $s_service->loadValidatedContact($contact);
                    }
                }

                // Send invalid contacts to the Data Quality Profile table (DQP)
                $s_service->moveInvalidContactsToDQP();

                // Save all changes
                $s_service->flushAll();

                $this->service->finishProgressBar();
                $this->service->finish();
            }else{
                $this->service->write('Task is Already Running');
            }
        }catch (\Exception $e){
            $this->service->throwError($e);
        }
    }
} 