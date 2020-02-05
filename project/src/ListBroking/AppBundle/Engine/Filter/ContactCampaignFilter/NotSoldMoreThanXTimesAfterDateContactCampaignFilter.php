<?php

namespace ListBroking\AppBundle\Engine\Filter\ContactCampaignFilter;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Engine\Filter\ContactCampaignFilterInterface;
use ListBroking\AppBundle\Enum\ConditionOperatorEnum;
use ListBroking\AppBundle\Form\FiltersType;

class NotSoldMoreThanXTimesAfterDateContactCampaignFilter implements ContactCampaignFilterInterface
{

    /**
     * @inheritdoc
     */
    public function addFilter(Andx $andX, QueryBuilder $qb, $filters)
    {
        foreach ($filters as $filter) {
            // Validate the Filter
            FiltersType::validateFilter($filter);

            switch ($filter['field']) {
                case 'max_times_sold':
                    $parameterName = 'contact_campaigns_max_sold_times';

                    foreach ($filter['value'] as $value) {
                        $qb->having(sprintf('count(contact_campaigns.id) < :%s', $parameterName));
                        $qb->setParameter($parameterName, $value);
                    }
                    break;
                default:
                    $parameterName     = sprintf('contact_campaigns_%s', $filter['field']);
                    $conditionOperator = ConditionOperatorEnum::convertNameToOperator($filter['opt']);

                    foreach ($filter['value'] as $value) {
                        $andX->add(
                            $qb->expr()->andX(
                                sprintf(
                                    'contact_campaigns.%s %s :%s',
                                    $filter['field'],
                                    $conditionOperator,
                                    $parameterName
                                )
                            )
                        );

                        $qb->setParameter($parameterName, $value);
                    }
                    break;
            }
        }
    }

}
