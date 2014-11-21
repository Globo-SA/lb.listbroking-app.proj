<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\LeadValidator;


use ListBroking\LeadBundle\Entity\Source;
use ListBroking\LeadBundle\Exception\LeadValidationException;

class SourceValidator extends BaseValidator {
    /**
     * @param $service
     * @param $lead
     */
    public function __construct($service, $lead)
    {
        parent::__construct($service, $lead);
    }

    /**
     * @param $validations
     * @return mixed
     * @throws LeadValidationException
     */
    public function validate($validations){
        // If external_id is set will try to find it by external_id
        if (isset($this->lead['external_id'])) {
            parent::validateEmpty($this->lead['external_id'], 'external_id');
            $source = $this->checkExternalIdExistance($this->lead['external_id'], true);
            if (isset($source)) {
                $validations['source'] = $source;
            } elseif (isset($this->lead['source_name'])) {      // if it wasn't found by ID will try to find it by source name
                $source = $this->service->getSourceByName($this->lead['source_name'], true);
                if (isset($source)){
                    $validations['source'] = $this->service->addSource($source);
                } else {
                    // if it doesn't exist will add the new source if it has both source_name and external_id
                    parent::validateEmpty($this->lead['source_name'], 'source');
                    $validations['source'] = $this->lead['source_name'];
                    $source = new Source();
                    $source->setName($this->lead['source_name']);
                    $source->setIsActive(1);
                    $source->setCountry($validations['country']);
                    $source->setExternalId($this->lead['external_id']); // TODO: Add source_page_id to URL
                    $source->setOwner($validations['owner']);
                    $validations['source'] = $this->service->addSource($source);
                }
            } else {
                throw new LeadValidationException("The lead['external_id'] and lead['source_name'] must be both sent if the Source doesn't exist yet.");
            }
        } elseif(isset($this->lead['source_id'])) {     // if it has the source_id will get by it's ID instead
            parent::validateEmpty($this->lead['source_id'], 'source');
            $validations['source'] = $this->service->getSource($this->lead['source_id'], true);
            if ($validations['source'] == null){
                throw new LeadValidationException("The lead['source_id'] does not exist in sources list.");
            }
        } else {
            throw new LeadValidationException("The field lead['source_name/source_id'] must be sent.");
        }

        if (!isset($validations['source'])){
            throw new LeadValidationException("Source could not be obtained." . var_dump($validations['source']));
        }

        return $validations;
    }

    /**
     * @param $external_id
     * @return mixed
     */
    private function checkExternalIdExistance($external_id){
        return $this->service->getByExternalId($external_id, true);
    }
} 