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


use ListBroking\AdvancedConfigurationBundle\Service\ListBrokingAdvancedConfiguration;
use ListBroking\LockBundle\Service\LockService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RotateExpiredLocksCommand extends ContainerAwareCommand{


    private $adv_config;
    private $lock_service;

    function __construct(ListBrokingAdvancedConfiguration $adv_config, LockService $lock_service)
    {
        parent::__construct();
        $this->adv_config = $adv_config;
        $this->lock_service = $lock_service;
    }

    protected function configure(){
        $this
            ->setName('locks:rotate_expired')
            ->setDescription('Removes old Locks from the main lock table')
            ->addOption('days', null,  InputOption::VALUE_OPTIONAL, 'Rotate locks before X days')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $output->writeln("<info>RotateExpiredLocksCommand:</info> <comment>START</comment>");
        $days = $input->getOption('days');
        if($days == null){
            $days = $this->adv_config->get('locks.rotate_locks_before', 30); // Defaults to 30 days
        }

        $output->writeln("<info>RotateExpiredLocksCommand:</info> Moving locks from the last <comment>{$days} days</comment> to history.");

        $rows_moved = $this->lock_service->removeExpiredLocks($days);

        $output->writeln("<info>RotateExpiredLocksCommand:</info> Number of locks moved to history: <comment>{$rows_moved}</comment>");
        $output->writeln("<info>RotateExpiredLocksCommand:</info> <comment>END</comment>");
    }
}