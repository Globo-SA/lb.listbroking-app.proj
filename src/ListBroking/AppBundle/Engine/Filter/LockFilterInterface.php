<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Filter;

use Doctrine\ORM\Query\Expr\Andx;

use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Exception\InvalidFilterTypeException;

interface LockFilterInterface {

    /**
     * Contact Filter types
     */
    const NO_LOCKS_TYPE = 'no_locks';
    const RESERVED_LOCK_TYPE = 'reserved_lock';
    const CLIENT_LOCK_TYPE = 'client_lock';
    const CAMPAIGN_LOCK_TYPE = 'campaign_lock';
    const CATEGORY_LOCK_TYPE = 'category_lock';
    const SUB_CATEGORY_LOCK_TYPE = 'sub_category_lock';

    /**
     * @param Andx $andX
     * @param QueryBuilder $qb
     * @param $filters
     * @throws InvalidFilterObjectException
     * @throws InvalidFilterTypeException
     * @return mixed
     */
    public function addFilter (Andx $andX, QueryBuilder $qb, $filters);
} 