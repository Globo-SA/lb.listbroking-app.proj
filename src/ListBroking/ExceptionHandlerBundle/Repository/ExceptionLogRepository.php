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
use ListBroking\AppBundle\Behavior\DateSearchableRepositoryBehavior;

class ExceptionLogRepository extends EntityRepository {

    use DateSearchableRepositoryBehavior;

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

    /**
     * Cleanup records equal or older than id.
     * @param $id
     * @return mixed
     */
    public function cleanUp($id)
    {
        return $this->createQueryBuilder('el')
                    ->delete('ListBrokingExceptionHandlerBundle:ExceptionLog' ,'el')
                    ->where('el.id <= :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->execute();
    }

}