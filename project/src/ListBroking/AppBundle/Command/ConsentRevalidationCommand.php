<?php

namespace ListBroking\AppBundle\Command;

use ListBroking\AppBundle\Service\BusinessLogic\ConsentRevalidationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsentRevalidationCommand extends ContainerAwareCommand
{
    /**
     * @var ConsentRevalidationServiceInterface
     */
    private $consentRevalidationService;

    protected function configure()
    {
        $this->setName('listbroking:consent:revalidate')
            ->setDescription('Revalidate contacts')
            ->addOption('year', null, InputOption::VALUE_REQUIRED, 'Contacts year', 0)
            ->addOption('country', null, InputOption::VALUE_REQUIRED, 'Contacts country code', 'PT')
            ->addOption('owner', null, InputOption::VALUE_REQUIRED, 'Contacts owner', 'ADCLICK')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Max contacts to re-validate', 0)
            ->addOption('contact-id', null, InputOption::VALUE_OPTIONAL, 'Contact ID', 0);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->consentRevalidationService = $this->getContainer()->get('app.service.consent_revalidation');

        try {
            $contactsRevalidated = $this->consentRevalidationService->revalidateWithIVR(
                $input->getOption('year'),
                $input->getOption('country'),
                $input->getOption('owner'),
                $input->getOption('limit'),
                $input->getOption('contact-id')
            );

            $output->writeln(sprintf('%d contacts selected to revalidate', count($contactsRevalidated)));

        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
            $output->writeln($exception->getTraceAsString());
        }
    }
}
