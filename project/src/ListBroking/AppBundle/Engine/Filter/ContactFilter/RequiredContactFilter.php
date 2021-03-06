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
use ListBroking\AppBundle\Form\FiltersType;

class RequiredContactFilter implements ContactFilterInterface {

    /**
     * @inheritdoc
     */
    public function addFilter(Andx $andX, QueryBuilder $qb, $filters)
    {
        foreach($filters as $filter){

            // Validate the Filter
            FiltersType::validateFilter($filter);

            // Add the join alias
            $filter['field'] = 'contacts.' . $filter['field'];

            // Add Not null validation
            $andX->add(
                $qb->expr()->isNotNull($filter['field'])
            );
        }
    }
}