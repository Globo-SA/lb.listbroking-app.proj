<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Filter\ContactFilter;


use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;

use ListBroking\AppBundle\Engine\Filter\ContactFilterInterface;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Exception\InvalidFilterTypeException;

class BasicContactFilter implements ContactFilterInterface {

    /**
     * @param Andx $andx
     * @param QueryBuilder $qb
     * @param $filters
     * @throws InvalidFilterObjectException
     * @throws InvalidFilterTypeException
     * @return mixed
     */
    public function addFilter(Andx $andx, QueryBuilder $qb, $filters)
    {
        foreach($filters as $filter){

            // Add the join alias
            $filter['field'] = 'contacts.' . $filter['field'];

            // Generate an unique id for each filter to avoid collisions
            $uid = uniqid();

            // Validate filter array
            if(!array_key_exists('field', $filter)
                || !array_key_exists('opt', $filter)
                || !array_key_exists('value', $filter)
                || !is_array($filter['value'])
            ){
                throw new InvalidFilterObjectException(
                    'Invalid filter, must be: array(\'field\' => \'\', \'opt\' => \'\', \'value\' => array()), in ' .
                    __CLASS__ );
            }

            switch($filter['opt']){
                case 'equal':

                    // Equal
                    if(count($filter['value']) > 1){
                        $andx->add(
                            $qb->expr()->in($filter['field'], ":in_filter_{$uid}")
                        );
                        $qb->setParameter("in_filter_{$uid}", $filter['value']);
                    }
                    // IN
                    else{
                        $andx->add(
                            $qb->expr()->eq($filter['field'], ":equal_filter_{$uid}")
                        );
                        $qb->setParameter("equal_filter_{$uid}", $filter['value']);
                    }
                    break;
                case 'not_equal':

                    // NOT EQUAL
                    if(count($filter['value']) > 1){
                        $andx->add(
                            $qb->expr()->notIn($filter['field'], ":in_filter_{$uid}")
                        );
                        $qb->setParameter("in_filter_{$uid}", $filter['value']);
                    }
                    // NOT IN
                    else{
                        $andx->add(
                            $qb->expr()->neq($filter['field'], ":not_equal_filter_{$uid}")
                        );
                        $qb->setParameter("not_equal_filter_{$uid}", $filter['value']);
                    }
                    break;
                case 'between':
                    if(is_array($filter['value'])){
                        $orX = $qb->expr()->orX();

                        $i = 0;
                        foreach ($filter['value'] as $between)
                        {
                            $orX->add(
                                $qb->expr()->between(
                                    $filter['field'],
                                    ":between_filter_x_{$i}_{$uid}",
                                    ":between_filter_y_{$i}_{$uid}"
                                )
                            );
                            $qb->setParameter(":between_filter_x_{$i}_{$uid}", $between[0]);
                            $qb->setParameter(":between_filter_y_{$i}_{$uid}", $between[1]);

                            $i++;
                        }
                        $andx->add($orX);
                    }else{
                        // BETWEEN
                        $andx->add(
                            $qb->expr()->between(
                                $filter['field'],
                                ":between_filter_x_{$uid}",
                                ":between_filter_y_{$uid}"
                            )
                        );
                        $qb->setParameter(":between_filter_x_{$uid}", $filter['value'][0]);
                        $qb->setParameter(":between_filter_y_{$uid}", $filter['value'][1]);
                    }

                    break;
                default:
                    throw new InvalidFilterTypeException(
                        "\"{$filter['opt']}\" is an invalid filter option, in "
                        . __CLASS__);
                    break;
            }
        }
    }


}