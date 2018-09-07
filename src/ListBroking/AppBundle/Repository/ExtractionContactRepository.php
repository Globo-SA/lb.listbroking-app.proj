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
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Lead;
use PDO;

class ExtractionContactRepository extends EntityRepository implements ExtractionContactRepositoryInterface
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
     * Counts the Extraction Contacts of a given Extraction
     *
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function countExtractionContacts(Extraction $extraction)
    {
        return (int) $this->createQueryBuilder('ec')
                    ->select('count(ec.id)')
                    ->where('ec.extraction = :extraction')
                    ->setParameter('extraction', $extraction->getId())
                    ->getQuery()
                    ->getSingleScalarResult()
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

    /**
     * Finds extraction contacts of a given extraction with a limit and an ID offset
     *
     * @param Extraction $extraction
     * @param array     $headers
     * @param int        $limit
     * @param int        $offset
     *
     * @return array
     */
    public function findExtractionContactsWithIdOffset(Extraction $extraction, $headers, $limit, $offset)
    {
        $conn = $this->getEntityManager()
                     ->getConnection()
        ;

        $parameters = array(
            'extraction_id' => $extraction->getId(),
            'offset' => $offset
        );

        $composed_headers = $this->composeHeaders($headers);
        $find_extraciton_contacts_query = <<<SQL
            SELECT extraction_contact.id as extraction_contact_id, {$composed_headers}
            FROM extraction_contact extraction_contact
            LEFT JOIN contact contact ON contact.id = extraction_contact.contact_id
            LEFT JOIN lead lead ON lead.id = contact.lead_id
            LEFT JOIN source source ON source.id = contact.source_id
            LEFT JOIN owner owner ON owner.id = contact.owner_id
            JOIN sub_category sub_category ON sub_category.id = contact.sub_category_id
            LEFT JOIN category category ON category.id = sub_category.category_id
            JOIN gender gender ON gender.id = contact.gender_id
            LEFT JOIN district district ON district.id = contact.district_id
            LEFT JOIN county county ON county.id = contact.county_id
            LEFT JOIN parish parish ON parish.id = contact.parish_id
            JOIN country country ON country.id = contact.country_id
            WHERE extraction_contact.extraction_id = :extraction_id
            AND extraction_contact.id > :offset
            ORDER BY extraction_contact.id ASC
            LIMIT $limit
            ;
SQL;
        $statment = $conn->prepare($find_extraciton_contacts_query);
        $statment->execute($parameters);

        return $statment->fetchAll();
    }

    private function composeHeaders($headers){
        $composed_headers = array();
        foreach ($headers as $label => $field)
        {
            $composed_headers[] =  sprintf('%s as "%s"', $field, $label);
        }

        return implode(",", $composed_headers);
    }

    /**
     * Cleanup records from $maxExtractionId or older.
     * @param $maxExtractionId
     * @return mixed
     */
    public function cleanUp($maxExtractionId)
    {
        return $this->createQueryBuilder('ec')
                    ->delete('ListBrokingAppBundle:ExtractionContact' ,'ec')
                    ->where('ec.extraction_id <= :extraction')
                    ->setParameter('extraction', $maxExtractionId)
                    ->getQuery()
                    ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findContactExtractions(Contact $contact): array
    {
        return $this->createQueryBuilder('ec')
            ->select('co.date, e.name AS name, e.sold_at, ca.name AS campaign')
            ->innerJoin('ec.extraction', 'e')
            ->innerJoin('e.campaign', 'ca')
            ->innerJoin('ec.contact', 'co')
            ->where('ec.contact = :contactId')
            ->setParameter('contactId', $contact->getId())
            ->getQuery()
            ->execute(null, Query::HYDRATE_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    public function getLeadCampaignsGroupByClient(Lead $lead): array
    {
        $selectStatement = <<<SQL
  cl.id as client_id,
  l.phone as phone,
  group_concat(c.email) as emails,
  group_concat(ca.id) as campaigns_ids,
  group_concat(
    concat(ca.name, ' (Sold at: ', date(e.sold_at), ')')
  ) as campaigns_names
SQL;

        return $this->createQueryBuilder('ec')
            ->select($selectStatement)
            ->innerJoin('ec.extraction', 'e')
            ->innerJoin('e.campaign', 'ca')
            ->innerJoin('ca.client', 'cl')
            ->innerJoin('ec.contact', 'c')
            ->innerJoin('c.lead', 'l')
            ->where('l.id = (:leadId)')
            ->andWhere('e.sold_at is not null')
            ->groupBy('cl.id')
            ->setParameter('leadId', $lead->getId())
            ->getQuery()
            ->execute(null, Query::HYDRATE_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtractionContactsSoldByLead(Lead $lead, bool $sold): array
    {
        $qb = $this->createQueryBuilder('ec')
                   ->addSelect(['e', 'ca'])
                   ->innerJoin('ec.extraction', 'e')
                   ->innerJoin('e.campaign', 'ca')
                   ->innerJoin('ec.contact', 'c')
                   ->innerJoin('c.lead', 'l')
                   ->where('l.id = (:leadId)');

        if ($sold) {
            $qb->andWhere('e.sold_at is not null');
        }

        return $qb->setParameter('leadId', $lead->getId())
                  ->getQuery()
                  ->execute();
    }
}
