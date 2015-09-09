<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use ListBroking\AppBundle\Entity\Extraction;

class ExtractionContactRepository extends EntityRepository
{

    /**
     * Gets a Summary of Extraction Contacts
     *
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function findExtractionSummary (Extraction $extraction)
    {
        $qb = $this->createQueryBuilder('ec')
                   ->select('o.name, count(o.name) as total')
                   ->join('ec.contact', 'c')
                   ->join('c.owner', 'o')
                   ->where('ec.extraction = :extraction')
                   ->setParameter('extraction', $extraction->getId())
                   ->groupBy('c.owner')
        ;

        return $qb->getQuery()
                  ->execute(null, Query::HYDRATE_ARRAY)
            ;
    }

    /**
     * Gets the Extraction Contacts of a given Extraction
     *
     * @param Extraction $extraction
     * @param null       $limit
     * @param            $hydrationMethod
     *
     * @return mixed
     */
    public function findExtractionContacts (Extraction $extraction, $limit = null, $hydrationMethod = AbstractQuery::HYDRATE_OBJECT)
    {

        return $this->findExtractionContactsQuery($extraction, $limit)
                    ->execute(null, $hydrationMethod)
            ;
    }

    /**
     * Gets a Query object of the Extraction Contacts
     *
     * @param Extraction $extraction
     * @param null       $limit
     * @param null       $fetch_mode
     *
     * @return Query
     */
    public function findExtractionContactsQuery (Extraction $extraction, $limit = null, $fetch_mode = null)
    {
        $qb = $this->createQueryBuilder('ec')
                   ->join('ec.contact', 'c')
                   ->where('ec.extraction = :extraction')
                   ->setParameter('extraction', $extraction->getId())
        ;

        // Add Limit
        if ( $limit )
        {
            $qb->setMaxResults($limit);
        }
        $query = $qb->getQuery();

        switch ($fetch_mode)
        {
            case ClassMetadata::FETCH_EAGER:

                foreach ( $this->getClassMetadata()->getAssociationMappings() as $mapping )
                {
                    $query->setFetchMode($this->getClassName(), $mapping['fieldName'], ClassMetadata::FETCH_EAGER);
                }
                break;
            default:
                break;
        }

        return $query;
    }


}