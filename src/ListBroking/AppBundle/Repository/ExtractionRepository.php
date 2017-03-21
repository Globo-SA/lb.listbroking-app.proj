<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\ExtractionContact;
use ListBroking\AppBundle\Entity\Source;

class ExtractionRepository extends EntityRepository
{

    /**
     * Clones a given extraction and resets it's status
     *
     * @param Extraction $extraction
     *
     * @return Extraction
     */
    public function cloneExtraction (Extraction $extraction)
    {
        $clonedObject = clone $extraction;

        $clonedObject->setName($extraction->getName() . ' (duplicate)');
        $clonedObject->setStatus(Extraction::STATUS_FILTRATION);
        $clonedObject->setSoldAt(null);
        $clonedObject->getExtractionContacts()
                     ->clear()
        ;
        $clonedObject->getExtractionDeduplications()
                     ->clear()
        ;
        $clonedObject->setDeduplicationType(null);
        $clonedObject->setQuery(null);
        $clonedObject->setIsAlreadyExtracted(false);

        return $clonedObject;
    }

    /**
     * Associates multiple contacts to an extraction
     *
     * @param     $extraction Extraction
     * @param     $contacts
     * @param int $batch_size
     *
     * @return mixed
     */
    public function addContacts (Extraction $extraction, $contacts, $batch_size = 1000)
    {
        $extraction_id = $extraction->getId();

        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        // Remove old ExtractionContacts of current Extraction
        $connection->delete('extraction_contact', array('extraction_id' => $extraction_id));

        if(count($contacts) == 0)
        {
            return;
        }

        $batch = 1;

        // Add the new Contacts
        $batch_values = array();
        foreach ( $contacts as $contact )
        {
            $contact_id = $contact['contact_id'];

            $batch_values[] = sprintf('(%s,%s)', $extraction_id, $contact_id);

            if ( ($batch % $batch_size) === 0 )
            {
                $this->insertBatch($batch_values);

                // Reset Batch
                $batch_values = array();
                $batch = 1;
            }
            $batch++;
        }
        if(count($batch_values) > 0)
        {
            $this->insertBatch($batch_values);
        }
    }

    private function insertBatch ($batch_values)
    {
        $batch_string = implode(',', $batch_values);
        if(empty($batch_string))
        {
            return;
        }

        $sql = <<<SQL
                INSERT INTO extraction_contact (extraction_id, contact_id)
                VALUES {$batch_string}
SQL;
        $this->getEntityManager()
             ->getConnection()
             ->exec($sql)
        ;
    }

    /**
     * @param \DateTime|string $start_date
     * @param \DateTime|string $end_date
     * @param int $page
     * @param int $limit
     *
     * @return array|null
     */
    public function getActiveCampaigns($start_date, $end_date, $page = 1, $limit = 50)
    {
        if ($start_date instanceof \DateTime)
        {
            $start_date = $start_date->format('Y-m-d 0:0:0');
        }
        if ($end_date instanceof \DateTime)
        {
            $end_date = $end_date->format('Y-m-d 23:59:59');
        }
        if (!is_string($start_date) || !is_string($end_date))
        {
            return null;
        }

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder
            ->select(
                'c.id as campaign_id',
                'c.name as campaign_name',
                //'cl.external_id as client_id',
                'cl.name as client_name',
                'c.account_id as account_id',
                'c.account_name as account_name'
            )
            ->distinct(true)
            ->innerJoin(Campaign::class, 'c', Expr\Join::WITH, 'c = e.campaign')
            ->innerJoin(Client::class, 'cl', Expr\Join::WITH, 'cl = c.client')
            ->where('e.status = 3')
            ->andWhere('e.sold_at BETWEEN :date1 AND :date2')
            ->setParameter('date1', $start_date)
            ->setParameter('date2', $end_date)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;
        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param \DateTime|string $start_date
     * @param \DateTime|string $end_date
     * @param int $page
     * @param int $limit
     *
     * @return array|null
     */
    public function getRevenue($start_date, $end_date, $page = 1, $limit = 50)
    {
        if ($start_date instanceof \DateTime)
        {
            $start_date = $start_date->format('Y-m-d 0:0:0');
        }
        if ($end_date instanceof \DateTime)
        {
            $end_date = $end_date->format('Y-m-d 23:59:59');
        }
        if (!is_string($start_date) || !is_string($end_date))
        {
            return null;
        }

        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder
            ->select(
                //'e.id as extraction_id',
                'DATE(e.sold_at) as date',
                'e.payout * COUNT(e.id) as revenue',
                //'e.payout as payout',
                'COUNT(e.id) as quantity',
                'ca.id as campaign_id',
                'ca.name as campaign_name',
                'ca.account_id as account_id',
                'ca.account_name as account_name',
                //'cl.external_id as client_id',
                'cl.name as client_name',
                's.external_id as source_id',
                's.name as source_name'
            )
            ->innerJoin(Campaign::class, 'ca', Expr\Join::WITH, 'ca = e.campaign')
            ->innerJoin(Client::class, 'cl', Expr\Join::WITH, 'cl = ca.client')
            ->innerJoin(ExtractionContact::class, 'ec', Expr\Join::WITH, 'e = ec.extraction')
            ->innerJoin(Contact::class, 'co', Expr\Join::WITH, 'co = ec.contact')
            ->innerJoin(Source::class, 's', Expr\Join::WITH, 's = co.source')
            ->where('e.status = 3')
            ->andWhere('e.sold_at BETWEEN :date1 AND :date2')
            ->groupBy('e.id, s.name')
            ->setParameter('date1', $start_date)
            ->setParameter('date2', $end_date)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        return $queryBuilder->getQuery()->getArrayResult();
    }
}
