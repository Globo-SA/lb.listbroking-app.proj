<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use ListBroking\AppBundle\Entity\Contact;

class ContactRepository extends EntityRepository implements ContactRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): array
    {
        return $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('c')
                    ->from('ListBrokingAppBundle:Contact', 'c')
                    ->where('c.email = :email')
                    ->setParameter('email', $email)
                    ->getQuery()
                    ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdAndLead($id, $leadId)
    {
        return $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('c')
                    ->from('ListBrokingAppBundle:Contact', 'c')
                    ->where('c.id = :id')
                    ->andWhere('c.lead = :lead_id')
                    ->setParameter('id', $id)
                    ->setParameter('lead_id', $leadId)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByLeadAndEmailAndOwner($leadId, $email, $owner)
    {
        return $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('c')
                    ->from('ListBrokingAppBundle:Contact', 'c')
                    ->join('c.owner', 'o')
                    ->where('c.email = :email')
                    ->andWhere('c.lead = :lead_id')
                    ->andWhere('o.name = :owner_name')
                    ->setParameter('email', $email)
                    ->setParameter('lead_id', $leadId)
                    ->setParameter('owner_name', $owner)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function getRandomContactsWithoutConsentRevalidations(
        int $year,
        string $countryCode,
        string $owner,
        int $limit,
        int $contactId = null
    ): array {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c, l')
            ->from('ListBrokingAppBundle:Contact', 'c')
            ->join('c.lead', 'l')
            ->join('c.country', 'co')
            ->join('c.owner', 'o')
            ->join('c.source', 's')
            ->join('s.brand', 'b')
            ->leftJoin('c.consentRevalidations', 'r')
            ->where('c.is_clean = :is_clean')
            ->andWhere('l.is_ready_to_use = :is_ready_to_use')
            ->andWhere('l.in_opposition = :in_opposition')
            ->andWhere('year(c.date) = :year')
            ->andWhere('co.name = :country_code')
            ->andWhere('r.id is null')
            ->andWhere('o.name = :owner')
            ->andWhere('b.ivrAudioUrl is not null')
            ->andWhere('b.ivrAudioUrl <> \'\'')
            ->groupBy('l.id')
            ->orderBy('rand()')
            ->setParameter('is_clean', true)
            ->setParameter('is_ready_to_use', true)
            ->setParameter('in_opposition', false)
            ->setParameter('year', $year)
            ->setParameter('country_code', strtolower($countryCode))
            ->setParameter('owner', $owner);

        if ($contactId) {
            $query
                ->andWhere('c.id = :contact_id')
                ->setParameter('contact_id', $contactId);
        }

        return $query
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws OptimisticLockException
     */
    public function saveContact(Contact $contact): Contact
    {
        $this->getEntityManager()->persist($contact);
        $this->getEntityManager()->flush();

        return $contact;
    }
}
