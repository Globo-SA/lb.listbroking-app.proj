<?php
/**
 *
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Repository\ORM;

use Doctrine\ORM\AbstractQuery;
use ListBroking\CoreBundle\Repository\CountryRepositoryInterface;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;

class CountryRepository extends BaseEntityRepository implements CountryRepositoryInterface
{
    /**
     * @param $code
     * @param bool $hydrate
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountryByCode($code, $hydrate = false)
    {
        $query_builder = $this->createQueryBuilder()
            ->andWhere("{$this->alias()}.iso_code = :iso_code");

        $query_builder->setParameter('iso_code', $code);
        if ($hydrate){
            return $query_builder->getQuery()->getOneOrNullResult();
        }

        return $query_builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }
}