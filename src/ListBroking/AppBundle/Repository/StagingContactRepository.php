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
use ListBroking\AppBundle\Entity\Lock;
use ListBroking\AppBundle\Entity\StagingContact;

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

        // If the lead doesn't exist create a new one
        if (!$lead)
        {
            $lead = new Lead();
        }

        $lead->setPhone($s_contact->getPhone());
        $lead->setIsMobile($s_contact->getIsMobile());
        $lead->setInOpposition($s_contact->getInOpposition());
        $lead->setCountry($country);
        $em->persist($lead);

        $contact = $em->getRepository('ListBrokingAppBundle:Contact')->findOneBy(array(
            'id' => $s_contact->getContactId()
        ));


        // If the contact doesn't exist create a new one
        if (!$contact)
        {
            $contact = new Contact();
        }

        $contact->setEmail($s_contact->getEmail());

        if($s_contact->getFirstname()){

        }

        $contact->setExternalId($s_contact->getExternalId());
        $contact->setFirstname($s_contact->getFirstname());
        $contact->setLastname($s_contact->getLastname());
        $contact->setBirthdate(new \DateTime($s_contact->getBirthdate()));
        $contact->setAddress($s_contact->getAddress());
        $contact->setPostalcode1($s_contact->getPostalcode1());
        $contact->setPostalcode2($s_contact->getPostalcode2());
        $contact->setIpaddress($s_contact->getIpaddress());
        $contact->setDate($s_contact->getDate());

        $contact->setLead($lead);
        $contact->setSource($source);
        $contact->setOwner($owner);
        $contact->setSubCategory($sub_category);
        $contact->setGender($gender);
        $contact->setDistrict($district);
        $contact->setCounty($county);
        $contact->setParish($parish);
        $contact->setCountry($country);

        $contact->setValidations($s_contact->getValidations());
        $contact->setPostRequest($s_contact->getPostRequest());

        $now = new \DateTime();
        $initial_lock_expiration_date = $s_contact->getInitialLockExpirationDate();
        if($initial_lock_expiration_date > $now){
            $lock = new Lock();
            $lock->setType(Lock::TYPE_NO_LOCKS);
            $lock->setExpirationDate($initial_lock_expiration_date);
            $lead->addLock($lock);
        }

        $em->persist($contact);

//        $em->remove($s_contact);

        $em->flush();
    }
} 