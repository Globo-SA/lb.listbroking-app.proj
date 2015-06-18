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


use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Extraction;

class ContactRepository extends EntityRepository {

    public function findByLeadPhone($phone, $hydrate_mode){
        return $this->createQueryBuilder('e')
            ->join('e.lead', 'lead')
            ->andWhere("lead.phone = :phone")
            ->setParameter('phone', $phone)
            ->getQuery()
            ->execute(null, $hydrate_mode);
    }
} 