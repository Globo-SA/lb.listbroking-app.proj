<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service;

use ListBroking\AppBundle\Service\BaseService\BaseService;

class AppService extends BaseService implements AppServiceInterface {

    /**
     * @param $code
     * @param bool $hydrate
     * @return mixed
     */
    public function getCountryByCode($code, $hydrate = true)
    {
       $entities = $this->getEntities('country', $hydrate);
        foreach ($entities as $entity){
            if($hydrate){
                if($entity->getIsoCode() == $code){
                    return $entity;
                }
            } else{
                if($entity['iso_code'] == $code){
                    return $entity;
                }
            }
        }

        return null;
    }
}