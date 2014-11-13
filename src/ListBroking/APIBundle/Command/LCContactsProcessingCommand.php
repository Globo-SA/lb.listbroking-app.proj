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
    protected $api_service;

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
        $this->api_service = $this->getContainer()->get('listbroking.api.service');

        $sql= <<< SQL
            SELECT *
            FROM leadcentre_contacts
            WHERE is_processed = 0
            ORDER BY id ASC
            LIMIT {$max_contacts};
SQL;
        $result = $stmt->executeQuery($sql);
        $contacts = $result->fetchAll();
        $now_datetime = new \DateTime('now');
        foreach ($contacts as $contact){
            $lead = array(
                'lead' => array(
                    'contact_id'     => $contact['contact_id'],
                    'gender'         => $contact['gender'],
                    'email'          => $contact['email'],
                    'phone'          => $contact['phone'],
                    'firstname'      => $contact['firstname'],
                    'lastname'       => $contact['lastname'],
                    'birthdate'      => $contact['birthdate'],
                    'address'        => $contact['address'],
                    'country'        => $contact['country'],
                    'postalcode1'    => $contact['postalcode1'],
                    'postalcode2'    => $contact['postalcode1'],
                    'city'           => $contact['city'],
                    'ipaddress'      => $contact['ipaddress'],
                    'external_id'    => $contact['source_page_id'],
                    'source_name'    => $contact['source_page_domain'],
                    'sub_category'   => $contact['category'],
                    'extra_fields'   => $contact['extra_fields'],
                    'resting_date'   => $now_datetime->format('Y-m-d H:i:s'),
                    'owner_name'     => 'adclick'
                ),
                'token_name'             => 'adclick',
                'token'                  => 'ZDhjZmIxZGJiYzI1ODIzMDIyMjFjNTk1MGEwZTFlYjY5ODkwMzgyOTAzM2Y0YjY3M2U1MjFiNzc3NDM1OGU5Yg'
            );
            $token['name'] = $lead['token_name'];
            $token['key'] = $lead['token'];
            $this->api_service->setLead($lead);
            $this->api_service->setValidators();
            $response = $this->api_service->processRequest($token);
            var_dump($response);die;
        }
    }
}