<?php

/**
 *
 * @author     Diogo Basto <diogo.basto@smark.io>
 * @copyright  2017 Smarkio
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Behavior\DateSearchableRepositoryBehavior;

class StagingContactProcessedRepository extends EntityRepository
{

    use DateSearchableRepositoryBehavior;

    /**
     * Cleanup records equal or older than id.
     * @param $id
     * @return mixed
     */
    public function cleanUp($id)
    {
        return $this->createQueryBuilder('scp')
                    ->delete('ListBrokingAppBundle:StagingContactProcessed' ,'scp')
                    ->where('scp.id <= :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->execute();
    }

}

