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


use Doctrine\DBAL\Statement;
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
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute(null, AbstractQuery::HYDRATE_ARRAY);

        return $stmt->fetch();
    }

}