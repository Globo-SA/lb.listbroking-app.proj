<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\APIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LCContactsProcessingCommand extends ContainerAwareCommand {
    protected function configure(){
        $this
            ->setName('APIBundle:LCContactsProcessing')
            ->setDescription('Processes contacts exported from LC instance to LB database')
            ->addOption('max_contacts', null, InputOption::VALUE_OPTIONAL, 'If set, will override max_contacts to process by each run.', 10000)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $max_contacts = $input->getOption('max_contacts');
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $stmt = $em->getConnection();

        $sql= <<< SQL
            SELECT *
            FROM leadcentre_contacts
            WHERE is_processed = 0
            ORDER BY id ASC
            LIMIT {$max_contacts};
SQL;
        $result = $stmt->executeQuery($sql);
        $contacts = $result->fetch();
        var_dump($contacts);die;
        foreach ($contacts as $contact){

        }

        $stmt->beginTransaction();
        $stmt->commit();

    }
}