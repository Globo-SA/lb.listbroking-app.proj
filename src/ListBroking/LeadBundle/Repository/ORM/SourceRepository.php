<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Repository\ORM;


use Doctrine\ORM\AbstractQuery;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\LeadBundle\Repository\SourceRepositoryInterface;

class SourceRepository extends BaseEntityRepository implements SourceRepositoryInterface
{
    /**
     * @param $source_name
     * @param bool $hydrate
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSourceByName($source_name, $hydrate = false)
    {
        $query_builder = $this->createQueryBuilder()
            ->andWhere("{$this->alias()}.name = :name");

        $query_builder->setParameter('name', $source_name);
        if ($hydrate){
            return $query_builder->getQuery()->getOneOrNullResult();
        }

        return $query_builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }
} 