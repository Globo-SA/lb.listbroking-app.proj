<?php

namespace ListBroking\AppBundle\Command;

use ListBroking\AppBundle\Service\Helper\StatisticsServiceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListbrokingAudiencesCalculateStatsCommand extends ContainerAwareCommand
{
    /**
     * @var StatisticsServiceInterface
     */
    private $statisticsService;

    protected function configure()
    {
        $this
            ->setName('listbroking:audiences:calculate-stats')
            ->setDescription('Updates Audience Statistics')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // initialize services
        $this->statisticsService = $this->getContainer()->get('statistics');

        $this->statisticsService->calculateAudiences();
    }

}
