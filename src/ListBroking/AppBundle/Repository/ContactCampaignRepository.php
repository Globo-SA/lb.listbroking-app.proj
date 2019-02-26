<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ContactCampaignRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContactCampaignRepository extends EntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function generateHistory(int $extractionId): void
    {
        $connection = $this->getEntityManager()
                           ->getConnection();

        $query = 'INSERT INTO contact_campaign (contact_id, campaign_id, created_at)
                    SELECT ec.contact_id, ex.campaign_id, now()
                    FROM extraction_contact ec
                    JOIN extraction ex ON ex.id=ec.extraction_id
                    WHERE ec.extraction_id = :extraction_id';

        $statement = $connection->prepare($query);
        $statement->execute(
            [
                'extraction_id' => $extractionId,
            ]
        );
    }
}
