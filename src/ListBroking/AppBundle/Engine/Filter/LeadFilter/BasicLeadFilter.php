<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Filter\LeadFilter;


use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;

use ListBroking\AppBundle\Engine\Filter\LeadFilterInterface;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Exception\InvalidFilterTypeException;
use ListBroking\AppBundle\Form\FiltersType;

class BasicLeadFilter implements LeadFilterInterface {

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

            // Validate the Filter
            FiltersType::validateFilter($filter);

            $filter['field'] = 'leads.' . $filter['field'];

            // Generate an unique id for each filter to avoid collisions
            $uid = uniqid();

            switch($filter['opt']){
                case FiltersType::EQUAL_OPERATION:


                    $name =  "in_filter_{$uid}";

                    // Inclusion or Exclusion Filter
                    switch($filter['filter_operation']){
                        case FiltersType::INCLUSION_FILTER:
                            $andx->add(
                                $qb->expr()->in($filter['field'],':'. $name)
                            );
                            break;
                        case FiltersType::EXCLUSION_FILTER:
                            $andx->add(
                                $qb->expr()->notIn($filter['field'],':'. $name)
                            );
                            break;
                        default:
                            break;
                    }
                    $qb->setParameter($name, $filter['value']);

                    break;
                case FiltersType::BETWEEN_OPERATION:

                    $name_x =  "between_filter_x_{$uid}";
                    $name_y =  "between_filter_y_{$uid}";

                    // Inclusion or Exclusion Filter
                    switch($filter['filter_operation']){
                        case FiltersType::INCLUSION_FILTER:
                            $andx->add(
                                $qb->expr()->between(
                                    $filter['field'],
                                    ":between_filter_x_{$uid}",
                                    ":between_filter_y_{$uid}"
                                )
                            );
                            break;
                        case FiltersType::EXCLUSION_FILTER:
                            $andx->add(
                            // NOT BETWEEN EQ DOESN'T EXIST IN DOCTRINE QUERY BUILDER
                                $qb->expr()->not(
                                    $qb->expr()->between(
                                        $filter['field'],
                                        ":between_filter_x_{$uid}",
                                        ":between_filter_y_{$uid}"
                                    )
                                )
                            );

                            break;
                        default:
                            break;
                    }
                    $qb->setParameter($name_x, $filter['value'][0]);
                    $qb->setParameter($name_y, $filter['value'][1]);

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