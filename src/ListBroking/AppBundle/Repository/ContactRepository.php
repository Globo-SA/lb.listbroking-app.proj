<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;


use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Extraction;

class ContactRepository extends EntityRepository {

    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     * @param Extraction $extraction
     * @return mixed
     */
    public function getExtractionContacts(Extraction $extraction){

        $qb = $this->createQueryBuilder('e')
            ->join("e.extractions", 'extractions')
            ->where("extractions = :extraction")
            ->setParameter('extraction', $extraction);

        // Add contact Dimensions
        $contact_ass_map =  $meta = $this->getEntityManager()->getClassMetadata('ListBroking\AppBundle\Entity\Contact')->getAssociationNames();
        foreach ($contact_ass_map as $associations_mapping)
        {
            if(!in_array($associations_mapping, array('created_by', 'updated_by', 'extractions'))){
                $qb->leftJoin('e.' . $associations_mapping, $associations_mapping);
                $qb->addSelect($associations_mapping);
            }
            if($associations_mapping == 'sub_category'){
                $qb->join('sub_category.category', 'category');
                $qb->addSelect('category');
            }
        }

        return $qb->getQuery()->execute(null, AbstractQuery::HYDRATE_ARRAY);
    }


    public function findByLeadPhone($phone, $hydrate_mode){
        return $this->createQueryBuilder('e')
            ->join('e.lead', 'lead')
            ->andWhere("lead.phone = :phone")
            ->setParameter('phone', $phone)
            ->getQuery()
            ->execute(null, $hydrate_mode);
    }
} 