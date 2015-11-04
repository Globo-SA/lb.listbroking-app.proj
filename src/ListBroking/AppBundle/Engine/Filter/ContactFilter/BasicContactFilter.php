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
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

use ListBroking\AppBundle\Engine\Filter\ContactFilterInterface;
use ListBroking\AppBundle\Exception\InvalidFilterTypeException;
use ListBroking\AppBundle\Form\FiltersType;

class BasicContactFilter implements ContactFilterInterface {

    /**
     * @inheritdoc
     */
    public function addFilter(Andx $andx, QueryBuilder $qb, $filters)
    {
        $previous_field = '';
        foreach($filters as $filter){

            if($previous_field == $filter['field'])
            {
                $andx = $qb->expr()
                            ->orX()
                ;
            }
            $previous_field = $filter['field'];

            // Validate the Filter
            FiltersType::validateFilter($filter);

            // Add the join alias
            $filter['field'] = 'contacts.' . $filter['field'];

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