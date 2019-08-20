<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Command;

use Adclick\TaskControllerBundle\Service\TaskServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\StagingService;
use ListBroking\AppBundle\Service\BusinessLogic\StagingServiceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StagingContactsCleanUpCommand extends ContainerAwareCommand
{

    const MAX_RUNNING = 1;

    /**
     * @var TaskServiceInterface
     */
    private $taskService;

    /**
     * @var StagingServiceInterface
     */
    private $stagingContactService;

    protected function configure()
    {
        $this->setName('listbroking:staging:cleanup')
             ->setDescription('Cleans up the invalid contacts from the StagingContact table')
             ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max contacts to validate per task iteration', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->taskService           = $this->getContainer()->get('task');
        $this->stagingContactService = $this->getContainer()->get('app.service.staging');

        try {
            if (!$this->taskService->start($this, $input, $output, self::MAX_RUNNING)) {

                $this->taskService->write('Task is Already Running');

                return;
            }

            $limit = $input->getOption('limit');

            $this->taskService->write('Sending invalid contacts to the Data Quality Profile table (DQP)');

            $this->stagingContactService->moveInvalidContactsToDQP($limit);

            $this->taskService->finish();
        } catch (\Exception $e) {
            $this->taskService->throwError($e);
            $this->taskService->write($e->getTraceAsString());
        }
    }
}
