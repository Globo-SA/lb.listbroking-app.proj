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
use ListBroking\LeadBundle\Repository\LeadRepositoryInterface;

class LeadRepository extends BaseEntityRepository implements LeadRepositoryInterface
{
    /**
     * @param $phone
     * @param bool $hydrate
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLeadByPhone($phone, $hydrate = false)
    {
        $query_builder = $this->createQueryBuilder()
            ->andWhere("{$this->alias()}.phone = :phone");

        $query_builder->setParameter('phone', $phone);
        if ($hydrate){
            return $query_builder->getQuery()->getOneOrNullResult();
        }

        return $query_builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }
}