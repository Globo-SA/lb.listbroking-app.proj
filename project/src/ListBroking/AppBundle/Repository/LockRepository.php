<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Behavior\DateSearchableRepositoryBehavior;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Lock;

class LockRepository extends EntityRepository
{

    use DateSearchableRepositoryBehavior;

    /**
     * Generates the necessary locks for a given Extraction
     *
     * @param Extraction $extraction
     * @param            $lock_types
     * @param            $lock_time
     */
    public function generateLocks (Extraction $extraction, $lock_types, $lock_time)
    {
        $connection = $this->getEntityManager()
                           ->getConnection()
        ;

        $extraction_id = $extraction->getId();

        foreach ( $lock_types as $lock_type )
        {
            $query = <<<SQL
                    INSERT INTO lb_lock (extraction_id, lead_id, client_id, campaign_id, category_id, sub_category_id, type, lock_date, expiration_date, created_at, updated_at)
                    SELECT ec.extraction_id, c.lead_id, camp.client_id, camp.id, sub_c.category_id, sub_c.id, :lock_type, :lock_date, :expiration_date, now(), now()
                    FROM extraction_contact ec
                    JOIN contact c on ec.contact_id= c.id
                    JOIN extraction ex on ec.extraction_id = ex.id
                    JOIN campaign camp on ex.campaign_id = camp.id
                    JOIN sub_category sub_c on c.sub_category_id = sub_c.id
                    WHERE ec.extraction_id = :extraction_id
SQL;

            $statement = $connection->prepare($query);
            $statement->execute(array(
                'lock_type'       => $lock_type,
                'lock_date'       => (new \DateTime())->format('Y-m-d'),
                'expiration_date' => (new \DateTime($lock_time))->format('Y-m-d'),
                'extraction_id'   => $extraction_id,
            ));
        }
    }

    /**
     * Override the classic comparision function for the search. In the locks table case,
     * we want to wipe data based on Expiration date instead of creation
     * @param Lock $entity
     * @param $date
     * @return bool
     */
    protected function __locateIdOnDateCompare($entity, $date)
    {
        return $entity->getExpirationDate() < $date;
    }

    /**
     * Cleanup records equal or older than id.
     * @param $id
     * @return mixed
     */
    public function cleanUp($id)
    {
        return $this->createQueryBuilder('l')
                    ->delete('ListBrokingAppBundle:Lock' ,'l')
                    ->where('l.id <= :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->execute();
    }
}