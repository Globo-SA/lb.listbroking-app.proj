<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Helper;


use ListBroking\AppBundle\Service\Base\BaseService;

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
                if($entity->getName() == $code){
                    return $entity;
                }
            } else{
                if($entity['name'] == $code){
                    return $entity;
                }
            }
        }

        return null;
    }

    /**
     * Gets a list of entities using the services
     * provided in various bundles
     * @param $type
     * @param $parent_type
     * @param $parent_id
     * @throws \Exception
     * @return mixed
     */
    public function getEntityList($type, $parent_type, $parent_id)
    {
        if (empty($type))
        {
            throw new \Exception("Type can not be empty", 400);
        }

        $list = array();
        switch ($type)
        {
            case 'client':
                $list = $this->getEntities('client', false);
                break;
            case 'campaign':
                $tmp_list = $this->getEntities('campaign', false);
                foreach ($tmp_list as $key => $obj)
                {
                    if(!empty($parent_id) && $obj['client_id'] == $parent_id){
                        $client = $this->getEntities('client', false);
                        $obj['name'] = $client['name'] . ' - ' . $obj['name'];

                        $list[] = $obj;
                    }
                }
                break;
            case 'extraction':
                $tmp_list = $this->getEntities('extraction', false);
                foreach ($tmp_list as $key => $obj)
                {
                    if(!empty($parent_id) && $obj['campaign_id'] == $parent_id){
                        $list[] = $obj;
                    }
                }
                break;
            case 'extraction_template':
                $list = $this->getEntities('extraction_template', false);
                break;
            default:
                throw new \Exception("Invalid List, {$type}", 400);
                break;
        }

        return $list;
    }
}