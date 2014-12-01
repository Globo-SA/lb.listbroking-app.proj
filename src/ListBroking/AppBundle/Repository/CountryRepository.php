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

use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository{

    /**
     * @param $code
     * @param $hydrate_mode
     * @internal param bool $hydrate
     * @return mixed
     */
    public function getCountryByCode($code, $hydrate_mode)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.iso_code = :iso_code')
            ->setParameter('iso_code', $code)
            ->getQuery()
            ->getOneOrNullResult($hydrate_mode);
    }
}