<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Filter\ContactFilter;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Engine\Filter\ContactFilterInterface;
use ListBroking\AppBundle\Enum\ConditionOperatorEnum;
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
                case ConditionOperatorEnum::CONDITION_OPERATOR_NAME_EQUAL:

                    $name = str_replace('.', '_', sprintf("in_filter_%s_%s", $filter['filter_operation'], $filter['field']));

                    // Inclusion or Exclusion Filter
                    switch ( $filter['filter_operation'] )
                    {
                        case FiltersType::INCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expressions'] = $qb->expr()
                                                                                                                           ->in($filter['field'], ':' . $name)
                            ;
                            break;
                        case FiltersType::EXCLUSION_FILTER:
                            $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expressions'] = $qb->expr()
                                                                                                                           ->notIn($filter['field'], ':' . $name)
                            ;
                            break;
                        default:
                            break;
                    }
                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['parameter_id'] = $name;
                    $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['values'][] = $filter['value'];
                    break;
                case ConditionOperatorEnum::CONDITION_OPERATOR_NAME_BETWEEN:

                    foreach ( $filter['value'] as $key => $value )
                    {
                        $name_x = str_replace('.', '_', sprintf("between_filter_x_%s_%s_%s", $filter['filter_operation'], $filter['field'], $key));
                        $name_y = str_replace('.', '_', sprintf("between_filter_y_%s_%s_%s", $filter['filter_operation'], $filter['field'], $key));

                        // Inclusion or Exclusion Filter
                        switch ( $filter['filter_operation'] )
                        {
                            case FiltersType::INCLUSION_FILTER:
                                $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expressions'][] = $qb->expr()
                                                                                                                                 ->between($filter['field'], ":" . $name_x, ":" . $name_y)
                                ;
                                break;
                            case FiltersType::EXCLUSION_FILTER:
                                $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['expressions'][] = $qb->expr()
                                                                                                                                 ->not($qb->expr()
                                                                                                                                          ->between($filter['field'], ":" . $name_x, ":" . $name_y))
                                ;
                                break;
                            default:
                                break;
                        }

                        $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['parameter_id'][] = $name_x;
                        $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['parameter_id'][] = $name_y;

                        $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['values'][$name_x] = $value[0];
                        $exp_bucket[$filter['field']][$filter['opt']][$filter['filter_operation']]['values'][$name_y] = $value[1];
                    }
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
                $expr = $qb->expr()
                           ->andX()
                ;

                if ( array_key_exists('inclusion', $filter_operations) )
                {
                    $inclusion_filter = $filter_operations['inclusion'];
                    $inclusion_or = $qb->expr()
                                       ->orX()
                    ;

                    $this->addExpressions($expr, $inclusion_or, $inclusion_filter['expressions']);
                    $this->addParameters($qb, $operation, $inclusion_filter['parameter_id'], $inclusion_filter['values']);
                }

                if ( array_key_exists('exclusion', $filter_operations) )
                {
                    $exclusion_filter = $filter_operations['exclusion'];
                    $exclusion_and = $qb->expr()
                                        ->andX()
                    ;

                    $this->addExpressions($expr, $exclusion_and, $exclusion_filter['expressions']);
                    $this->addParameters($qb, $operation, $exclusion_filter['parameter_id'], $exclusion_filter['values']);
                }
                $orX->add($expr);
            }
            $andX->add($orX);
        }
    }

    /**
     * Add the given parts to the QueryBuilder
     *
     * @param Composite    $expr
     * @param Composite    $comp
     * @param              $parts
     */
    private function addExpressions (Composite $expr, Composite $comp, $parts)
    {
        if ( is_array($parts) )
        {
            foreach ( $parts as $part )
            {
                $comp->add($part);
            }
            $expr->add($comp);

            return;
        }
        $expr->add($parts);
    }

    /**
     * Add the given parameters to the QueryBuilder
     *
     * @param QueryBuilder $qb
     * @param string       $operation
     * @param string|array $parameter_id
     * @param array        $values
     */
    private function addParameters (QueryBuilder $qb, $operation, $parameter_id, $values)
    {
        switch ( $operation )
        {
            case ConditionOperatorEnum::CONDITION_OPERATOR_NAME_EQUAL:

                if ( count($values) == 1 )
                {
                    $qb->setParameter($parameter_id, $values[0]);
                }
                else
                {
                    $final_values = array();
                    foreach ( $values as $value )
                    {
                        $final_values[] = $value[0];
                    }
                    $qb->setParameter($parameter_id, $final_values);
                }
                break;
            case ConditionOperatorEnum::CONDITION_OPERATOR_NAME_BETWEEN:
                foreach ( $parameter_id as $id )
                {
                    $qb->setParameter($id, $values[$id]);
                }
                break;
        }
    }
}
