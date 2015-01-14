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


    /**
     * Group leads by lock and count them
     * @return array
     */
    public function countByLock()
    {
        $sql = <<<SQL
            SELECT (count(tmp.lead_id) - count(tmp.lock_id)) as open_leads, count(tmp.lock_id) as lock_leads
            FROM (
                SELECT l.id as lead_id, lo.id as lock_id
                from lead l
                left join lb_lock lo on l.id = lo.lead_id
                GROUP BY l.id
            ) as tmp
SQL;

        /** @var Statement $stmt */
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute(null, AbstractQuery::HYDRATE_ARRAY);

        return $stmt->fetch();
    }

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