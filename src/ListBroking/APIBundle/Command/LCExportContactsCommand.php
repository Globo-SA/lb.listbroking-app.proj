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
use Symfony\Component\Config\Definition\Exception\Exception;
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
            ->addOption('max_contacts', null, InputOption::VALUE_OPTIONAL, 'If set, will override max_contacts to process by each run.', 10000)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $max_contacts = $input->getOption('max_contacts');
        try {
            $this->startMysqliConnection();
        } catch (MysqliException $e){
            $message = $e->getMessage();
            $output->writeln("<info>LCExportContacts:</info> <comment>Problem: $message</comment>");
            die($e);
        }
        try{
            $from = $this->getLastContactId();
            $from = $from['contact_id'] + 1;
        } catch (MysqliException $e){
            $message = $e->getMessage();
            $output->writeln("<info>LCExportContacts:</info> <comment>Problem: $message</comment>");
        }
        if (is_null($from)){
            $from = 2000000; //min contact_id that has country (aprox.)
        }
        $to = $from + $max_contacts;
        $output->writeln("<info>LCExportContacts:</info> <comment>Getting contacts from $from to $to</comment>");
        do {
            $sql = "SELECT c.id as contact_id, c.email, c.gender, c.firstname, c.lastname, c.birthdate,
                            if(ifnull(c.phone, '') != '', c.phone, ccdth.contact_detail_value) as phone,
                            if(ifnull(ccdth3.contact_detail_value, '') != '', ccdth3.contact_detail_value, CONCAT(ifnull(ccdth4.contact_detail_value, ''), ' ', ifnull(ccdth5.contact_detail_value, '') ,' ',ifnull(ccdth6.contact_detail_value, ''))) as address,
                            if(ifnull(ccdth1.contact_detail_value,'') != '', ccdth1.contact_detail_value, ccdth2.contact_detail_value) as country,
                            c.postalcode1, c.postalcode2, c.city,
                            c.ipaddress, c.source_page_id, sp.domain, sp.category
                FROM contact_hist c
                INNER JOIN contact_integration_status_hist cis ON (cis.contact_id = c.id AND cis.status = 1 AND cis.client_id=1)
                INNER JOIN source_page sp ON (sp.id = c.source_page_id)
                LEFT JOIN contact_contact_detail_type_hist ccdth ON (ccdth.contact_id = c.id and ccdth.contact_detail_type_id = 85)
                LEFT JOIN contact_contact_detail_type_hist ccdth1 ON (ccdth1.contact_id = c.id and ccdth1.contact_detail_type_id = 35)
                LEFT JOIN contact_contact_detail_type_hist ccdth2 ON (ccdth2.contact_id = c.id and ccdth2.contact_detail_type_id = 37)
                LEFT JOIN contact_contact_detail_type_hist ccdth3 ON (ccdth3.contact_id = c.id and ccdth3.contact_detail_type_id = 49)
                LEFT JOIN contact_contact_detail_type_hist ccdth4 ON (ccdth4.contact_id = c.id and ccdth4.contact_detail_type_id = 50)
                LEFT JOIN contact_contact_detail_type_hist ccdth5 ON (ccdth5.contact_id = c.id and ccdth5.contact_detail_type_id = 51)
                LEFT JOIN contact_contact_detail_type_hist ccdth6 ON (ccdth6.contact_id = c.id and ccdth6.contact_detail_type_id = 52)
                WHERE is_valid = 1
                AND ifnull(c.email, '') != ''
                AND (ifnull(c.phone, '') != '' OR ifnull(ccdth.contact_detail_value, '')!='')
                AND (ifnull(ccdth1.contact_detail_value, '') != '' OR ifnull(ccdth2.contact_detail_value, '') != '')
                AND ifnull(c.source_page_id, '') != ''
                AND c.email NOT LIKE '%%adctst.com%%'
                AND c.id between {$from} and {$to}
                LIMIT {$max_contacts}";
                $from += $max_contacts;
                $to += $max_contacts;
            try{
                $this->result = $this->executeQuery($sql);
            } catch (MysqliException $e){
                $message = $e->getMessage();
                $output->writeln("<info>LCExportContacts:</info> <comment>Could not get contacts. $message</comment>");
            } catch (APIException $e) {
                $message = $e->getMessage();
                $output->writeln("<info>LCExportContacts:</info> <comment>Could not get contacts. $message</comment>");
            }
            $count = $this->result->num_rows;
        } while (!$this->result->num_rows);
        $output->writeln("<info>LCExportContacts:</info> <comment>Found $count results between contact_id's $from to $to</comment>");
        try{
            $this->saveLeadsToListBroking();
        } catch (MysqliException $e){
            $message = $e->getMessage();
            $output->writeln("<info>LCExportContacts:</info> <comment>Problem: $message</comment>");
        } catch (APIException $e) {
            $message = $e->getMessage();
            $output->writeln("<info>LCExportContacts:</info> <comment>Problem: $message</comment>");
        }
    }

    protected function executeQuery($query){
        $query = $this->mysql_connection->query($query);
        if ($query === FALSE ) {
            throw new MysqliException("Mysql Error: " . $this->mysql_connection->errno . " " . $this->mysql_connection->error);
        } elseif ($query->num_rows == 0){
            return $query;
        }
        return $query;
    }

    protected function startMysqliConnection(){
        $this->mysql_connection = new \mysqli('adclickinstance2-listbroking.c1xjt8uy0oz0.us-east-1.rds.amazonaws.com', 'lcdbuser', 'Ay570ln3', 'lcdb', 3306);
        if ($this->mysql_connection->connect_errno) {
            throw new MysqliException("Failed to connect to MySQL: (" . $this->mysql_connection->connect_errno . ") " . $this->mysql_connection->connect_error);
        } else {
            return true;
        }
    }

    protected function getLastContactId(){
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $stmt = $em->getConnection();
        $sql = "SELECT MAX(contact_id) as contact_id
                FROM leadcentre_contacts
                ORDER BY contact_id DESC
                LIMIT 1";
        $result = $stmt->executeQuery($sql);
        return $result->fetch();
    }

    protected function saveLeadsToListBroking(){
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $stmt = $em->getConnection();
        foreach ($this->result as $contact){
            $sql = "SELECT contact_detail_value
                FROM contact_contact_detail_type
                WHERE contact_id = " . $contact['contact_id'] ."
                AND contact_detail_type_id not IN (85, 35, 37, 49, 50, 51, 52)"; // EXCLUDE MAIN ccdts ALREADY RETRIEVED TO SAVE SPACE
            $st_ccdts = $this->executeQuery($sql);
            $ccdts = NULL;
            if ($st_ccdts->num_rows){
                $flag = true;
                foreach ($st_ccdts as $ccdt){
                    if ($flag){
                        $ccdts = $ccdt;
                        $flag = false;
                        continue;
                    }
                    $ccdts .= ',' . $ccdt;
                }
                if (!empty($ccdts)){
                    $ccdts = json_encode($ccdts);
                }
            }
            foreach ($contact as $key => $value){
                $value = str_replace(' ', '', $value);
                if (empty($value)){
                    $contact[$key] = NULL;
                }
            }
            $stmt->beginTransaction();
            try {
                $sql =
                    "INSERT INTO leadcentre_contacts
                        (
                        contact_id,
                        email,
                        gender,
                        firstname,
                        lastname,
                        birthdate,
                        phone,
                        address,
                        country,
                        postalcode1,
                        postalcode2,
                        city,
                        ipaddress,
                        source_page_id,
                        source_page_domain,
                        category,
                        extra_fields)
                VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );";        // TODO: check source page category (which one is it) and check for all the field names that com on the $contact array
                $stmt2 = $stmt->prepare($sql);
                $i=1;
                foreach ($contact as $key => $value){
                    $stmt2->bindParam($i, $contact[$key]);
                    $i++;
                }
                $stmt2->bindParam($i, $ccdts);
                $result = $stmt2->execute();
                $stmt->commit();
            } catch (Exception $e){
                $stmt->rollBack();
                throw $e;
            }
        }

    }

    protected function getLeadsExtraFields(){

    }
} 