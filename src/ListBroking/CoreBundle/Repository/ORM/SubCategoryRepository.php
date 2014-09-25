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

use ListBroking\CoreBundle\Repository\SubCategoryRepositoryInterface;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;

class SubCategoryRepository extends BaseEntityRepository implements SubCategoryRepositoryInterface
{
    /**
     * @return mixed
     */
    public function findAll()
    {
        return $this->createQueryBuilder()->getQuery()->getResult();
    }
} 