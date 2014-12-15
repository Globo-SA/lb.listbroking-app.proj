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


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->service = $this->getContainer()->get('task');
//        try{
//            if($this->service->start($this, $input, $output, DeduplicateContactsCommand::MAX_RUNNING)){
                $this->getContainer()->get('staging')->validateStagingContacts(1);
//            }else{
//                $this->service->write('Task is Already Running');
//            }
//        }catch (\Exception $e){
//            $this->service->throwError($e);
//        }
    }
} 