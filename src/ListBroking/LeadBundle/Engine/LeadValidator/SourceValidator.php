<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Engine\LeadValidator;


use ListBroking\LeadBundle\Entity\Source;
use ListBroking\LeadBundle\Exception\LeadValidationException;
use Symfony\Component\HttpFoundation\Request;

class SourceValidator extends BaseValidator {
    /**
     * @param $service
     * @param Request $request
     */
    public function __construct($service, Request $request)
    {
        parent::__construct($service, $request);
    }

    /**
     * @param $validations
     * @return mixed
     * @throws LeadValidationException
     */
    public function validate($validations){
        if (isset($this->lead['source_name'])) {
            parent::validateEmpty($this->lead['source_name'], 'source');
            $validations['source'] = $this->service->getSourceByName($this->lead['source_name'], true);
            if ($validations['source'] == null){
                if (isset($this->lead['source_page_id'])){
                    parent::validateEmpty($this->lead['source_page_id'], 'source_page_id');
                    $validations['source'] = $this->lead['source_name'];
                    $source = new Source();
                    $source->setName($this->lead['source_name']);
                    $source->setIsActive(1);
                    $source->setCountry($validations['country']);
                    $source->setLcSourcePageId($this->lead['source_page_id']); // TODO: Add source_page_id to URL
                    $source->setOwner($validations['owner']);
                    $this->service->addSource($source);
                } else {
                    throw new LeadValidationException("The lead['source_page_id'] must be sent if the Source doesn't exist yet.");
                }
;            }
        } elseif(isset($this->lead['source_id'])) {
            parent::validateEmpty($this->lead['source_id'], 'source');
            $validations['source'] = $this->service->getSource($this->lead['source_id'], true);
            if ($validations['source'] == null){
                throw new LeadValidationException("The lead['source_id'] does not exist in sources list.");
            }
        } else{
            throw new LeadValidationException("The field lead['source_name/source_id'] must be sent.");
        }

        return $validations;
    }
} 