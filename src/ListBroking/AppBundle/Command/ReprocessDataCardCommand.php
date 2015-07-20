<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use ListBroking\TaskControllerBundle\Service\TaskServiceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReprocessDataCardCommand extends ContainerAwareCommand
{

    const MAX_RUNNING = 1;

    /**
     * @var TaskServiceInterface
     */
    private $service;

    protected function configure ()
    {
        $this->setName('listbroking:data-card:reprocess')
             ->setDescription('Reprocesses the data card')
             ->addOption('country', 'c', InputOption::VALUE_OPTIONAL, 'Reprocess a single country', null)
             ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, 'Date to start fetching values', date("Y-m-d"))
             ->addOption('months', 'm', InputOption::VALUE_OPTIONAL, 'Number of sequential months to reprocess', 1)
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
            if ( $this->service->start($this, $input, $output, self::MAX_RUNNING) )
            {
                $now_time = time();
                $end_time = strtotime($input->getOption('date'));
                if ( $end_time > $now_time )
                {
                    $this->service->write("You can't reprocess future dates");
                    $this->service->finish();

                    return;
                }

                $months = intval($input->getOption('months'));
                for ( $i = 0; $i < $months; $i++ )
                {
                    $current_time = strtotime("-{$i} months", $end_time);
                    $current_start_date = date('Y-m-01', $current_time);;
                    $current_end_date = date('Y-m-t', $current_time);
                }
                $this->service->finish();
            }
            else
            {
                $this->service->write('Task is Already Running');
            }
        }
        catch ( \Exception $e )
        {
            $this->service->throwError($e);
        }
    }
} 