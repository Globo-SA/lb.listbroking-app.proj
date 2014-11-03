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


use Doctrine\DBAL\Driver\Mysqli\MysqliException;
use ListBroking\APIBundle\Exception\APIException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LCExportContactsCommand extends ContainerAwareCommand {
    protected $mysql_connection;
    protected $result;

    protected function configure(){
        $this
            ->setName('APIBundle:LCExportContacts')
            ->setDescription('Exports contacts from LC instance to LB database')
            ->addOption('max_contacts', null, InputOption::VALUE_OPTIONAL, 'If set, will override max_contacts to process by each run.', 1000)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->startMysqliConnection();
//        $sql = "SELECT cont.id, cont.email, cont.firstname, cont.lastname, cont.birthdate, cont.gender, cont.postalcode1, cont.postalcode2, cont.city, cont.phone,
//                        cont.ipaddress, cont.source_page_id
//                FROM
//                (SELECT c.id, c.email, c.firstname, c.lastname, c.birthdate, c.gender, c.postalcode1, c.postalcode2, c.city, c.phone,
//                        c.ipaddress, c.source_page_id
//                FROM contact_hist c
//                INNER JOIN contact_integration_status_hist cis ON (cis.contact_id = c.id AND cis.status = 1)
//                LEFT JOIN source_page sp ON (sp.id = c.source_page_id)
//                WHERE is_valid = 1
//                AND ifnull(c.email, '') != ''
//                AND ifnull(c.firstname, '') != ''
//                AND ifnull(c.lastname, '') != ''
//                AND ifnull(c.birthdate, '') != ''
//                AND ifnull(c.gender, '') != ''
//                AND ifnull(c.postalcode1, '') != ''
//                AND ifnull(c.postalcode2, '') != ''
//                AND ifnull(c.city, '') != ''
//                AND ifnull(c.phone, '') != ''
//                AND ifnull(c.ipaddress, '') != ''
//                AND ifnull(c.source_page_id, '') != ''
//                ORDER by c.id desc
//                LIMIT 100
//                ) cont
//                GROUP by cont.email, cont.source_page_id
//                LIMIT 100;";
        $last_contact_id = $this->getLastContactId();
        $sql = "SELECT c.id, c.email, c.firstname, c.lastname, c.birthdate, c.gender, c.postalcode1, c.postalcode2, c.city, c.phone,
                        c.ipaddress, c.source_page_id, sp.domain
                FROM contact_hist c
                INNER JOIN contact_integration_status_hist cis ON (cis.contact_id = c.id AND cis.status = 1)
                INNER JOIN source_page sp ON (sp.id = c.source_page_id)
                WHERE is_valid = 1
                AND ifnull(c.email, '') != ''
                AND ifnull(c.firstname, '') != ''
                AND ifnull(c.lastname, '') != ''
                AND ifnull(c.birthdate, '') != ''
                AND ifnull(c.gender, '') != ''
                AND ifnull(c.postalcode1, '') != ''
                AND ifnull(c.postalcode2, '') != ''
                AND ifnull(c.city, '') != ''
                AND ifnull(c.phone, '') != ''
                AND ifnull(c.ipaddress, '') != ''
                AND ifnull(c.source_page_id, '') != ''
                AND c.id > {$last_contact_id}
                LIMIT 100";
        try{
            $stmt = $this->executeQuery($sql);
            $this->result = $stmt->fetch_assoc();
            $this->saveLeadsToListBroking();
            var_dump($this->result);die;
        } catch (MysqliException $e){
            echo $e->getMessage();
        } catch (APIException $e) {
            echo "Could not save contacts to LB table. " . $e->getMessage();
        }
    }

    protected function executeQuery($query){
        $query = $this->mysql_connection->prepare($query);
        if ($query === FALSE ) {
            throw new MysqliException("Mysql Error: " . $this->mysql_connection->errno . " " . $this->mysql_connection->error);
        } elseif ($query->num_rows == 0){
            throw new MysqliException("Mysql Error: No results found");
        }
        return $query->execute();
    }

    protected function startMysqliConnection(){
        $this->mysql_connection = new \mysqli('adclickinstance2-listbroking.c1xjt8uy0oz0.us-east-1.rds.amazonaws.com', 'lcdbuser', 'Ay570ln3', 'lcdb');
        if ($this->mysql_connection->connect_errno) {
            die("Failed to connect to MySQL: (" . $this->mysql_connection->connect_errno . ") " . $this->mysql_connection->connect_error);
        } else {
            return true;
        }
    }

    protected function getLastContactId(){
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $stmt = $em->getConnection();
        $sql = "SELECT MIN(contact_id)
                FROM listbroking_contacts
                WHERE is_processed=0
                LIMIT 1";
        $result = $stmt->executeQuery($sql);
        return $result->fetch;
    }

    protected function saveLeadsToListBroking(){
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $stmt = $em->getConnection();
        $stmt->executeQuery("START TRANSACTION;");
        foreach ($this->result as $contact){
            var_dump($contact);die;
            $sql = "SELECT contact_detail_value
                FROM contact_contact_detail_value
                WHERE contact_id = " . $contact['id'];
            $ccdts = $stmt->execute($sql);
            $ccdts = json_encode($ccdts);
            $sql = "INSERT INTO leadcentre_contacts
                        (contact_id, email, gender, firstname, lastname, birthdate, phone, address, country, postalcode1, postalcode2, city, district, county, parish, ipaddress, source_page_id, source_page_domain, category, extra_fields)
                VALUES (
                    {$contact['id']},
                    {$contact['email']},
                    {$contact['gender']},
                    {$contact['firstname']},
                    {$contact['lastname']},
                    {$contact['birthdate']},
                    {$contact['phone']},
                    {$contact['address']},
                    {$contact['country']},
                    {$contact['postalcode1']},
                    {$contact['postalcode2']},
                    {$contact['city']},
                    {$contact['district']},
                    {$contact['county']},
                    {$contact['parish']},
                    {$contact['ipaddress']},
                    {$contact['source_page_id']},
                    {$contact['domain']},
                    {$contact['category']},
                    {$ccdts}
                );";        // TODO: check category (which one is it) and check for all the field names that com on the $contact array
            $result = $stmt->execute($sql);
        }
        $stmt->executeQuery("COMMIT;");
    }

    protected function getLeadsExtraFields(){

    }
} 