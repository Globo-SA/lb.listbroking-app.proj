<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Engine\FilterType;



use ESO\Doctrine\ORM\QueryBuilder;
use ListBroking\LockBundle\Engine\FilterInterface;

class ClientFilter  implements FilterInterface {

    private $type_id;

    public function __construct($type_id)
    {
        $this->type_id = $type_id;
    }

    /**
     * The ID of the type
     * @return mixed
     */
    public function typeId(){
        return $this->$type_id;
    }

    /**
     * Add a join to the QueryBuilder
     * with the filter options
     * @param QueryBuilder $qb
     * @param $filter
     * @return mixed
     */
    public function addJoin(QueryBuilder $qb, $filter)
    {
        // TODO: Implement addJoin() method.
    }
} 