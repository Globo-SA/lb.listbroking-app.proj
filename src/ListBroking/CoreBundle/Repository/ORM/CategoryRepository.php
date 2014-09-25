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

use ListBroking\CoreBundle\Repository\CategoryRepositoryInterface;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;

class CategoryRepository extends BaseEntityRepository implements CategoryRepositoryInterface
{

    /**
     * @return mixed
     */
    public function findAll()
    {
        return $this->createQueryBuilder()->getQuery()->getResult();
    }
}