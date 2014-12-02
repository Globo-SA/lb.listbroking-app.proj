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


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ExceptionLogRepository extends EntityRepository {


    public function findLastExceptions($days, $hydrate = true){

        $hydration = Query::HYDRATE_OBJECT;
        if(!$hydrate){
            $hydration = Query::HYDRATE_ARRAY;
        }

        return $this->createQueryBuilder('e')
            ->andWhere('e.created_at >= :this_week')
            ->setParameter('this_week',$days)
            ->orderBy('e.id','DESC')
            ->getQuery()
            ->setMaxResults(5)
            ->getResult($hydration);
    }
} 