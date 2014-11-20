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


use Doctrine\ORM\Query;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\ExtractionBundle\Entity\Extraction;
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


    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     * @param Extraction $extraction
     * @return mixed
     */
    public function getExtractionContacts(Extraction $extraction){
        $qb = $this->createQueryBuilder();

        $qb->join("{$this->getAlias()}.extractions", 'extractions');
        $qb->where("extractions = :extraction");
        $qb->setParameter('extraction', $extraction);

        // Add contact Dimensions
        $contact_ass_map =  $meta = $this->entityManager->getClassMetadata($this->entity_class)->getAssociationNames();
        foreach ($contact_ass_map as $associations_mapping)
        {
            if(!in_array($associations_mapping, array('created_by', 'updated_by', 'extractions'))){
                $qb->leftJoin($this->alias() . '.' . $associations_mapping, $associations_mapping);
                $qb->addSelect($associations_mapping);
            }
            if($associations_mapping == 'sub_category'){
                $qb->join('sub_category.category', 'category');
                $qb->addSelect('category');
            }
        }
        return $qb->getQuery()->execute(null, Query::HYDRATE_ARRAY);
    }
} 