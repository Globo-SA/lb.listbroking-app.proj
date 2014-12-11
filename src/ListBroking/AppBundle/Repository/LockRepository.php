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

class LockRepository extends EntityRepository {

    //TODO: Check if needed
//
//    /**
//     * Finds all locks of a given lead
//     * @param $ids
//     * @return mixed
//     */
//    public function findByLead($ids)
//    {
//        // TODO: Implement WHERE lead.id IN ($ids)
//
//        /**
//         * This find method needs to use a RAW QUERY to only
//         * fetch the relations IDs and not the objects
//         * Note: Yeah Doctrine sucks sometimes!!!
//         */
//        $sql = <<<SQL
//                SELECT * FROM lb_lock
//SQL;
//
//        /** @var Statement $stmt */
//        $stmt = $this->entityManager->getConnection()->prepare($sql);
//        $stmt->execute(null, AbstractQuery::HYDRATE_ARRAY);
//
//        return $stmt->fetchAll();
//    }
//
//    /**
//     * Removes locks by expiration date
//     * NOTE: An EventListener is used to send
//     * the locks to a _log table before there are removed
//     * @param $days
//     * @return int
//     */
//    public function removeByExpirationDate($days)
//    {
//
//        /** @var Connection $conn */
//        $conn = $this->entityManager->getConnection();
//
//        $column_names = implode(',', $this->getEntityColumns());
//
//        $sql = <<<SQL
//            INSERT INTO lb_lock_history ({$column_names})
//            SELECT {$column_names}
//            FROM lb_lock
//            WHERE expiration_date < :expiration_date;
//
//            DELETE FROM lb_lock
//            WHERE expiration_date < :expiration_date;
//SQL;
//        $stmt = $conn->prepare($sql);
//        $stmt->execute(array(
//            'expiration_date' => date("Y-m-d h:i:s", strtotime("- {$days} days"))
//        ));
//
//        return $stmt->rowCount();
//    }
} 