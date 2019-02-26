<?php

namespace ListBroking\AppBundle\Engine\Filter;

use Doctrine\ORM\Query\Expr\Andx;

use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Exception\InvalidFilterTypeException;

interface ContactCampaignFilterInterface {

    /**
     * ContactCampaign Filter types
     */
    const NOT_SOLD_MORE_THAN_X_TIMES_AFTER_DATE_TYPE = 'not_sold_more_than_x_times_after_date';

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
