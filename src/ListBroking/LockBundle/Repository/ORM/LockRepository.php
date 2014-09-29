<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Repository\ORM;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\AbstractQuery;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\LockBundle\Repository\LockRepositoryInterface;

class LockRepository extends BaseEntityRepository implements LockRepositoryInterface {

    /**
     * Finds all locks of a given lead
     * @param $ids
     * @return mixed
     */
    public function findByLead($ids)
    {
        // TODO: Implement WHERE lead.id IN ($ids)

        /**
         * This find method needs to use a RAW QUERY to only
         * fetch the relations IDs and not the objects
         * Note: Yeah Doctrine sucks sometimes!!!
         */
        $sql = <<<SQL
                SELECT * FROM lb_lock
SQL;

        /** @var Statement $stmt */
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute(null, AbstractQuery::HYDRATE_ARRAY);

        return $stmt->fetchAll();
    }


}