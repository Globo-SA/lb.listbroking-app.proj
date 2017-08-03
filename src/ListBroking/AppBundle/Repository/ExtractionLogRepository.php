<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionLog;

class ExtractionLogRepository extends EntityRepository
{

    /**
     * Finds the last ExtractionLog for a given Extraction
     *
     * @param Extraction $extraction
     * @param            $limit
     *
     * @return ExtractionLog[]
     */
    public function findLastExtractionLog (Extraction $extraction, $limit)
    {
        return $this->createQueryBuilder('el')
            ->where('el.extraction = :extraction')
            ->orderBy('el.id', 'DESC')
            ->setMaxResults($limit)

            ->setParameter('extraction', $extraction)
            ->getQuery()
            ->execute(null, AbstractQuery::HYDRATE_ARRAY)
            ;
    }

    /**
     * Cleanup records from $maxExtractionId or older.
     * @param $maxExtractionId
     * @return mixed
     */
    public function cleanUp($maxExtractionId)
    {
        return $this->createQueryBuilder('el')
            ->delete('ListBrokingAppBundle:ExtractionLog' ,'el')
            ->where('el.extraction_id <= :extraction')
            ->setParameter('extraction', $maxExtractionId)
            ->getQuery()
            ->execute();
    }
}