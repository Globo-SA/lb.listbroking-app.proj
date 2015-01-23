<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExceptionHandlerBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ExceptionLogRepository extends EntityRepository {

    const LIFETIME = 3600;

    /**
     * Gets the last exceptions by a min date
     * @param $limit
     * @return mixed
     */
    public function findLastExceptions($limit){

        return $this->createQueryBuilder('e')
            ->orderBy('e.id','DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

}