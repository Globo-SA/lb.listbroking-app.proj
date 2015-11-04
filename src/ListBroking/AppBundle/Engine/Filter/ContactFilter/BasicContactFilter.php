<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Filter\ContactFilter;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Engine\Filter\ContactFilterInterface;
use ListBroking\AppBundle\Form\FiltersType;

class BasicContactFilter implements ContactFilterInterface
{

    /**
     * @inheritdoc
     */
    public function addFilter (Andx $andx, QueryBuilder $qb, $filters)
    {
        $exp_bucket = array();
        $params_bucket = array();
        foreach ( $filters as $filter )
        {
            // Validate the Filter
            FiltersType::validateFilter($filter);

            // Add the join alias
            $filter['field'] = 'contacts.' . $filter['field'];

            switch ( $filter['opt'] )
            {
                case FiltersType::EQUAL_OPERATION:

                    $name =  str_replace('.','_' ,sprintf("in_filter_%s", $filter['field']));

                    // Inclusion or Exclusion Filter
                    switch($filter['filter_operation']){
                        case FiltersType::INCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expression'] = $qb->expr()->in($filter['field'],':'. $name);
                            break;
                        case FiltersType::EXCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expression'] = $qb->expr()->notIn($filter['field'],':'. $name);
                            break;
                        default:
                            break;
                    }
                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['parameter_id'] = $name;
                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['values'][] = $filter['value'][0];
                    break;
                case FiltersType::BETWEEN_OPERATION:

                    $name_x =  str_replace('.','_', sprintf("between_filter_x_%s",$filter['field']));
                    $name_y =  str_replace('.','_', sprintf("between_filter_y_%s",$filter['field']));

                    // Inclusion or Exclusion Filter
                    switch($filter['filter_operation']){
                        case FiltersType::INCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expression'] = $qb->expr()->between(
                                $filter['field'],
                                ":".$name_x,
                                ":".$name_y
                            );
                            break;
                        case FiltersType::EXCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expression'] = $qb->expr()->not(
                                $qb->expr()->between(
                                    $filter['field'],
                                    ":".$name_x,
                                    ":".$name_y
                                )
                            );
                            break;
                        default:
                            break;
                    }

                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['parameter_id']['x'] = $name_x;
                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['parameter_id']['y'] = $name_y;

                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['values']['x'] = $filter['value'][0];
                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['values']['y'] = $filter['value'][1];
                    break;
            }
        }
        foreach ( $exp_bucket as $field => $operations )
        {
            $orX = $qb->expr()->orX();
            foreach ( $operations as $operation => $filter_types )
            {
                foreach ( $filter_types as $filter_type => $filter )
                {
                    $expression = $filter['expression'];
                    $orX->add($expression);

                    $parameter_id = $filter['parameter_id'];
                    $values = $filter['values'];
                    switch($operation)
                    {
                        case FiltersType::EQUAL_OPERATION:
                            $qb->setParameter($parameter_id, $values);
                            break;
                        case FiltersType::BETWEEN_OPERATION:
                            $qb->setParameter($parameter_id['x'], $values['x']);
                            $qb->setParameter($parameter_id['y'], $values['y']);
                            break;
                    }
                }
            }
            $andx->add($orX);
        }
    }

}