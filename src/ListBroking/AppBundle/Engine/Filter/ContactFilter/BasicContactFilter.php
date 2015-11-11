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
    public function addFilter (Andx $andX, QueryBuilder $qb, $filters)
    {
        $exp_bucket = array();
        foreach ( $filters as $filter )
        {
            // Validate the Filter
            FiltersType::validateFilter($filter);

            // Add the join alias
            $filter['field'] = 'contacts.' . $filter['field'];

            switch ( $filter['opt'] )
            {
                case FiltersType::EQUAL_OPERATION:

                    $name = str_replace('.', '_', sprintf("in_filter_%s_%s", $filter['filter_operation'], $filter['field']));

                    // Inclusion or Exclusion Filter
                    switch ( $filter['filter_operation'] )
                    {
                        case FiltersType::INCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expression'] = $qb->expr()
                                                                                                                          ->in($filter['field'], ':' . $name)
                            ;
                            break;
                        case FiltersType::EXCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expression'] = $qb->expr()
                                                                                                                          ->notIn($filter['field'], ':' . $name)
                            ;
                            break;
                        default:
                            break;
                    }
                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['parameter_id'] = $name;
                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['values'][] = $filter['value'];
                    break;
                case FiltersType::BETWEEN_OPERATION:

                    $name_x = str_replace('.', '_', sprintf("between_filter_x_%s_%s", $filter['filter_operation'], $filter['field']));
                    $name_y = str_replace('.', '_', sprintf("between_filter_y_%s_%s", $filter['filter_operation'], $filter['field']));

                    // Inclusion or Exclusion Filter
                    switch ( $filter['filter_operation'] )
                    {
                        case FiltersType::INCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expression'] = $qb->expr()
                                                                                                                          ->between($filter['field'], ":" . $name_x, ":" . $name_y)
                            ;
                            break;
                        case FiltersType::EXCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expression'] = $qb->expr()
                                                                                                                          ->not($qb->expr()
                                                                                                                                   ->between($filter['field'], ":" . $name_x, ":" . $name_y))
                            ;
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

        /**
         * @var  string $field the name of the filed (postalcode1, country)
         */
        foreach ( $exp_bucket as $field => $operations )
        {
            $orX = $qb->expr()
                      ->orX()
            ;

            /**
             * @var string $operation operation type (IN, BETWEEN,etc)
             */
            foreach ( $operations as $operation => $filter_operations )
            {
                /** @var AndX $expressions */
                $expressions = $qb->expr()
                                  ->andX()
                ;

                if ( array_key_exists('inclusion', $filter_operations) )
                {
                    $inclusion_filter = $filter_operations['inclusion'];
                    $expressions->add($inclusion_filter['expression']);

                    $this->addParameters($qb, $operation, $inclusion_filter['parameter_id'], $inclusion_filter['values']);
                }

                if ( array_key_exists('exclusion', $filter_operations) )
                {
                    $exclusion_filter = $filter_operations['exclusion'];
                    $expressions->add($exclusion_filter['expression']);

                    $this->addParameters($qb, $operation, $exclusion_filter['parameter_id'], $exclusion_filter['values']);
                }
                $orX->add($expressions);
            }
            $andX->add($orX);
        }
    }

    /**
     * Add the given parameters to the QueryBuilder
     *
     * @param QueryBuilder $qb
     * @param string       $operation
     * @param string       $parameter_id
     * @param array        $values
     */
    private function addParameters (QueryBuilder $qb, $operation, $parameter_id, $values)
    {
        switch ( $operation )
        {
            case FiltersType::EQUAL_OPERATION:

                if(count($values) == 1)
                {
                    $qb->setParameter($parameter_id, $values[0]);
                }else
                {
                    $final_values = array();
                    foreach ( $values as $value )
                    {
                        $final_values[] = $value[0];
                    }
                    $qb->setParameter($parameter_id, $final_values);
                }
                break;
            case FiltersType::BETWEEN_OPERATION:
                $qb->setParameter($parameter_id['x'], $values['x']);
                $qb->setParameter($parameter_id['y'], $values['y']);
                break;
        }
    }

}