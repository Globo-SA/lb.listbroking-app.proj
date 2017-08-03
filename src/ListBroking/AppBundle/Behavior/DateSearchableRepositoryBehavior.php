<?php

/**
 *
 * @author     Diogo Basto <diogo.basto@smark.io>
 * @copyright  2017 Smarkio
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Behavior;

trait DateSearchableRepositoryBehavior
{

    /**
     * Returns the first ID or entity created after $date.
     * In case of using ID, it is not guaranteed the specific ID will exist.
     * @param \DateTime $date
     * @param bool $returnEntity return an entity or it's ID
     *
     * @return null|int|object
     */
    public function locateIdOnDate($date, $returnEntity = false)
    {
        if (! ($date instanceof \DateTime) )
        {
            return null;
        }
        //get the max Id
        $lastEntity = $this->createQueryBuilder('e')
                           ->orderBy('e.id', 'DESC')
                           ->setMaxResults(1)
                           ->getQuery()
                           ->getOneOrNullResult()
        ;
        if (!$lastEntity || !$this->__locateIdOnDateCompare($lastEntity, $date))
        {
            return null;
        }
        $firstEntity = $this->createQueryBuilder('e')
                            ->orderBy('e.id', 'ASC')
                            ->setMaxResults(1)
                            ->getQuery()
                            ->getOneOrNullResult()
        ;
        if (!$this->__locateIdOnDateCompare($firstEntity, $date))
        {
            return $returnEntity ? $firstEntity : $firstEntity->getId();
        }

        $lastId = $lastEntity->getId();
        $firstId = $firstEntity->getId();
        $entity = $this->__locateIdOnDateBSearch($firstId, $lastId, $date);
        return $returnEntity ? $entity : $entity->getId();
    }

    /**
     * Internal function that will perform a binary search on the database
     * @param $low
     * @param $high
     * @param $date
     * @return mixed
     */
    private function __locateIdOnDateBSearch($low, $high, $date)
    {
        $pivot = floor(($high + $low) / 2);


        $entity = $this->createQueryBuilder('e')
                       ->where('e.id >= :pivot')
                       ->orderBy('e.id', 'ASC')
                       ->setParameter('pivot', $pivot)
                       ->setMaxResults(1)
                       ->getQuery()
                       ->getOneOrNullResult()
        ;
        $entityId = $entity->getId();

        if ($pivot <= $low)
        {
            //if the current entity is lower than our limit, fetch the next entity.
            if ($this->__locateIdOnDateCompare($entity, $date))
            {
                return $this->createQueryBuilder('e')
                           ->where('e.id >= :pivot')
                           ->orderBy('e.id', 'ASC')
                           ->setParameter('pivot', $pivot+1)
                           ->setMaxResults(1)
                           ->getQuery()
                           ->getOneOrNullResult();
            }
            else
            {
                return $entity ;
            }
        }
        else if ($this->__locateIdOnDateCompare($entity, $date))
        {
            //using $entityId here because it's the first record with ID bigger than pivot.
            return $this->__locateIdOnDateBSearch($entityId + 1, $high, $date);
        }
        else
        {
            return $this->__locateIdOnDateBSearch($low, $pivot, $date);
        }
    }

    /**
     * Function to compare date with createdAt field. Override this function to compare against a different field
     * @param $entity
     * @param $date
     * @return bool
     */
    protected function __locateIdOnDateCompare($entity, $date)
    {
        return $entity->getCreatedAt() < $date;
    }
}