<?php

namespace ListBroking\AppBundle\Service\Helper;

use DateTime;
use Doctrine\ORM\Query;
use ListBroking\AppBundle\Form\DataCardFilterType;
use ListBroking\AppBundle\Model\AudiencesFilter;
use ListBroking\AppBundle\Repository\AudiencesStatsRepositoryInterface;
use ListBroking\AppBundle\Repository\ContactRepositoryInterface;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\Base\BaseServiceInterface;

class StatisticsService extends BaseService implements StatisticsServiceInterface
{
    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @var AudiencesStatsRepositoryInterface
     */
    private $audiencesStatsRepository;

    /**
     * StatisticsService constructor.
     *
     * @param ContactRepositoryInterface        $contactRepository
     * @param AudiencesStatsRepositoryInterface $audiencesStatsRepository
     */
    public function __construct(
        ContactRepositoryInterface $contactRepository,
        AudiencesStatsRepositoryInterface $audiencesStatsRepository
    ) {
        $this->contactRepository        = $contactRepository;
        $this->audiencesStatsRepository = $audiencesStatsRepository;
    }

    /**
     * @inheritdoc
     */
    public function generateStatisticsQuery($data)
    {
        if (empty($data)) {
            return [];
        }

        $cache_id = md5(serialize($data));
        if ($this->doctrine_cache->contains($cache_id)) {
            return $this->doctrine_cache->fetch($cache_id);
        }
        $qb     = $this->contactRepository->createQueryBuilder('c')
                                          ->select('count(c.id) as total');
        $fields = [];
        foreach ($data as $key => $values) {
            $info = explode('_', $key, 2);

            $type  = $info[0];
            $field = $info[1];

            switch ($type) {
                case DataCardFilterType::ENTITY_TYPE:
                    foreach ($values as $value) {
                        $qb->andWhere(
                            $qb->expr()
                               ->in("c.{$field}", ":{$field}")
                        )
                           ->setParameter($field, $value);
                    }
                    break;
                case DataCardFilterType::CONTACT_INFO_TYPE:
                    if ($field === 'date') {
                        $dates = explode('-', str_replace(' ', '', $values[$field]));

                        $from = (new \DateTime($dates[0]))->format('Y-m-d');
                        if (count($dates) > 1) {
                            $to = (new \DateTime($dates[1]))->format('Y-m-d');

                            $qb->andWhere($qb->expr()->between("c.{$field}", "'{$from}'", "'{$to}'"));
                            break;
                        }

                        $qb->andWhere($qb->expr()->eq("c.{$field}", "'{$from}'"));
                        break;
                    }

                    break;
                case DataCardFilterType::AGGREGATION_TYPE:
                    if ($values) {
                        if ($field === 'is_mobile') {
                            $qb->addSelect('CASE WHEN(lead.is_mobile = 1) THEN \'YES\' ELSE \'NO\' as is_mobile');
                            $qb->join('c.lead', 'lead');
                            $qb->addGroupBy('is_mobile');

                            break;
                        }

                        if ($field == 'date') {
                            $aggregation_name = sprintf("%s_by_%s", $field, $values);

                            $qb->addSelect("YEAR(c.date) as year");
                            $qb->addSelect(sprintf("%s(c.date) as %s", $values, $aggregation_name));

                            $qb->addGroupBy($aggregation_name);

                            break;
                        }

                        if (in_array($field, ['postalcode1', 'postalcode2'])) {
                            $qb->addSelect("c.{$field} as {$field}");
                            $qb->addGroupBy($field);

                            break;
                        }

                        // JOIN the aggregation association
                        if (!in_array($field, $fields)) {
                            $qb->addSelect("{$field}.name as {$field}_name");
                            $qb->leftJoin("c.{$field}", $field);
                            $qb->addGroupBy("c.{$field}");

                            $fields[] = $field;
                        }
                    }
                    break;
                case DataCardFilterType::AVAILABILITY_TYPE:
                    if ($values) {
                        $new_field = "has_{$field}";
                        $qb->addSelect("CASE WHEN(c.{$field} > '') THEN 'YES' ELSE 'NO' as {$new_field}");
                        $qb->addGroupBy($new_field);
                    }
                    break;
                default:
                    break;
            }
        }

        $this->doctrine_cache->save(
            $cache_id,
            $qb->getQuery()
               ->execute(null, Query::HYDRATE_ARRAY),
            BaseServiceInterface::CACHE_TTL
        );

        return $this->doctrine_cache->fetch($cache_id);
    }

    /**
     * @inheritDoc
     */
    public function calculateAudiences(): void
    {
        // clean previous data
        $this->audiencesStatsRepository->truncate();

        // insert updated data
//        $restingTimeInMonths = str_replace('+', '', $this->findConfig('lock.time'));
        $restingTimeInMonths = '1week';
        $minimumRestingDate  = new DateTime();
        $minimumRestingDate->modify(sprintf('-%s', $restingTimeInMonths));

        $this->audiencesStatsRepository->updateStats($minimumRestingDate->format('Y-m-d'));
    }

    /**
     * @inheritDoc
     */
    public function getAudiences(AudiencesFilter $filter): array
    {
        return $this->audiencesStatsRepository->getAudiences($filter);
    }
}
