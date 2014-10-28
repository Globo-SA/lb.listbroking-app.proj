<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Engine\CoreValidator;


use ListBroking\CoreBundle\Exception\CoreValidationException;
use Symfony\Component\HttpFoundation\Request;

class CategoryValidator extends BaseValidator {
    public function __construct($service, Request $request)
    {
        parent::__construct($service, $request);
    }

    public function validate($validations){
        if (isset($this->lead['sub_category'])){
            parent::validateEmpty($this->lead['sub_category'], 'sub_category');
            $categories = $this->service->getSubcategoryList();
            foreach ($categories as $category){
                if (strtolower($category['name']) == strtolower($this->lead['sub_category'])){
                    $validations['sub_category'] = $this->service->getSubCategory($category['id'], true);
                }
            }

            if (!isset($validations['sub_category']) || $validations['sub_category'] == null){
                throw new CoreValidationException("Contact must have a sub_category that exists. Given " . $this->lead['sub_category'] . " allowed " . $categories[0]['name']);
            }
        } else {
            throw new CoreValidationException("Contact must have a sub_category.");
        }


        return $validations;
    }
} 