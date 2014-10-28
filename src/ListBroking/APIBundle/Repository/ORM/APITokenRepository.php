<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\APIBundle\Repository\ORM;

use Doctrine\ORM\AbstractQuery;
use ListBroking\APIBundle\Repository\APITokenRepositoryInterface;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;

class APITokenRepository extends BaseEntityRepository implements APITokenRepositoryInterface {
    public function getTokenByName($name, $hydrate = false)
    {
        $query_builder = $this->createQueryBuilder()
            ->andWhere("{$this->alias()}.name = :name");

        $query_builder->setParameter('name', $name);
        if ($hydrate){
            return $query_builder->getQuery()->getOneOrNullResult();
        }

        return $query_builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }
} 