<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Contact;

class ContactRepository extends EntityRepository implements ContactRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findContactByEmail(string $email)
    {
        return $this->findOneBy([Contact::EMAIL_KEY => $email]);
    }
}