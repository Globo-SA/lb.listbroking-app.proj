<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\PHPExcel\FileHandler;

class StagingContactRepository extends EntityRepository
{

    /**
     * Moves invalid contacts to the DQP table
     * @throws \Doctrine\DBAL\DBALException
     */
    public function moveInvalidContactsToDQP(){

        $conn = $this->getEntityManager()->getConnection();
        $move_sql = <<<SQL
            INSERT INTO staging_contact_dqp
            SELECT *
            from staging_contact
            WHERE valid = 0 AND processed = 1

SQL;
        $conn->prepare($move_sql)
            ->execute();

        $del_sql = <<<SQL
            DELETE
            FROM staging_contact
            WHERE valid = 0 AND processed = 1
SQL;
        $conn->prepare($del_sql)
            ->execute();
    }

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     * @param StagingContact $s_contact
     */
    public function loadValidatedContact(StagingContact $s_contact){
        $em = $this->getEntityManager();

        /** @var StagingContact $s_contact */

        //Dimension Tables
        $source = $em->getRepository('ListBrokingAppBundle:Source')->findOneBy(
            array('name' => $s_contact->getSourceName()
            ));
        $owner = $em->getRepository('ListBrokingAppBundle:Owner')->findOneBy(
            array('name' => $s_contact->getOwner()
            ));
        $sub_category = $em->getRepository('ListBrokingAppBundle:SubCategory')->findOneBy(
            array('name' => $s_contact->getSubCategory()
            ));
        $gender = $em->getRepository('ListBrokingAppBundle:Gender')->findOneBy(
            array('name' => $s_contact->getGender()
            ));
        $district = $em->getRepository('ListBrokingAppBundle:District')->findOneBy(
            array('name' => $s_contact->getDistrict()
            ));
        $county = $em->getRepository('ListBrokingAppBundle:County')->findOneBy(
            array('name' => $s_contact->getCounty()
            ));
        $parish = $em->getRepository('ListBrokingAppBundle:Parish')->findOneBy(
            array('name' => $s_contact->getParish()
            ));
        $country = $em->getRepository('ListBrokingAppBundle:Country')->findOneBy(
            array('name' => $s_contact->getCountry()
            ));

        $lead = $em->getRepository('ListBrokingAppBundle:Lead')->findOneBy(array(
            'id' => $s_contact->getLeadId()
        ));

        // Facts Tables
        if (!$lead)
        {
            $lead = new Lead();
        }

        $lead->setPhone($s_contact->getPhone());
        $lead->setIsMobile($s_contact->getIsMobile());
        $lead->setInOpposition($s_contact->getInOpposition());
        $lead->setCountry($country);
        $em->persist($lead);

        $contact = new Contact();
        $contact->setEmail($s_contact->getEmail());
        $contact->setFirstname($s_contact->getFirstname());
        $contact->setLastname($s_contact->getLastname());
        $contact->setBirthdate(new \DateTime($s_contact->getBirthdate()));
        $contact->setAddress($s_contact->getAddress());
        $contact->setPostalcode1($s_contact->getPostalcode1());
        $contact->setPostalcode2($s_contact->getPostalcode2());
        $contact->setIpaddress($s_contact->getIpaddress());
        $contact->setValidations($s_contact->getValidations());

        $contact->setLead($lead);
        $contact->setSource($source);
        $contact->setOwner($owner);
        $contact->setSubCategory($sub_category);
        $contact->setGender($gender);
        $contact->setDistrict($district);
        $contact->setCounty($county);
        $contact->setParish($parish);
        $contact->setCountry($country);
        $em->persist($contact);

        $em->remove($s_contact);

        $em->flush();
    }
} 