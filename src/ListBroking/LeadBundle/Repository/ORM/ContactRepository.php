<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Repository\ORM;


use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\LeadBundle\Repository\ContactRepositoryInterface;

class ContactRepository extends BaseEntityRepository implements ContactRepositoryInterface {
    /**
     * @return mixed
     */
    public function getContactFields(){
        return $this->entityManager->getClassMetadata($this->entity_class)->columnNames;
    }

    /**
     * @param $email
     * @param bool $hydrate
     * @return array|mixed
     */
    public function getContactsByEmail($email, $hydrate = false)
    {
        $query_builder = $this->createQueryBuilder()
            ->andWhere("{$this->alias()}.email = :email");

        $query_builder->setParameter('email', $email);
        if ($hydrate){
            return $query_builder->getQuery()->execute();
        }

        return $query_builder->getQuery()->getArrayResult();
    }
} 