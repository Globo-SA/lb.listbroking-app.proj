<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RotateExpiredLocksCommand extends ContainerAwareCommand{


    protected function configure(){
        $this
            ->setName('lock:rotate_expired')
            ->setDescription('Removes old Locks from the main lock table')
            ->addOption('days', null,  InputOption::VALUE_OPTIONAL, 'Rotate locks before X days', 30)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $days = $input->getOption('days');

        $output->writeln('hello in: ' .$days);
    }

} 