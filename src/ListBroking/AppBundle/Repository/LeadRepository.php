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


use Doctrine\DBAL\Statement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

class LeadRepository extends EntityRepository {

    public function syncContactsWithOppositionLists(){

        // Set in_opposition for every matched phone
        $sql = <<<SQL
            UPDATE lead l
            JOIN opposition_list ol on l.phone = ol.phone
            SET l.in_opposition = 1
SQL;

        /** @var Statement $stmt */
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute(null, AbstractQuery::HYDRATE_ARRAY);
    }
} 