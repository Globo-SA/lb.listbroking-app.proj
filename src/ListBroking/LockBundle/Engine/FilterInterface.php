<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Engine;


use ESO\Doctrine\ORM\QueryBuilder;

interface FilterInterface {

    /**
     * The ID of the type
     * @return mixed
     */
    public function typeId();

    /**
     * Add a join to the QueryBuilder
     * with the filter options
     * @param QueryBuilder $qb
     * @param $filter
     * @return mixed
     */
   public function addJoin(QueryBuilder $qb, $filter);
} 