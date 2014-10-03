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
use ListBroking\LeadBundle\Repository\LeadRepositoryInterface;

class LeadRepository extends BaseEntityRepository implements LeadRepositoryInterface {

    /**
     * @return string
     */
    public function getAlias(){
        return $this->alias();
    }

} 