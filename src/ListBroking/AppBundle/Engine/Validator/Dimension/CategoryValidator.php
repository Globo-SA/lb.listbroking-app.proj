<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 *
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\Dimension;


use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\Category;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Entity\SubCategory;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class CategoryValidator implements ValidatorInterface {

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var bool
     */
    protected $is_required;

    /**
     * @param EntityManager $em
     * @param bool $is_required
     * @internal param EntityManager $service
     */
    function __construct(EntityManager $em, $is_required){
        $this->em = $em;
        $this->is_required = $is_required;
    }

    /**
     * Validates the contact against a set of rules
     * @param StagingContact $contact
     * @param $validations
     * @throws DimensionValidationException
     * @return mixed
     */
    public function validate(StagingContact $contact, &$validations)
    {
        $field = strtolower($contact->getSubCategory());
        if(empty($field)){
            if(!$this->is_required){
                return;
            }
            throw new DimensionValidationException('Empty sub_category field');
        }

        $sub_category = $this->em->getRepository('ListBrokingAppBundle:SubCategory')->findOneBy(array(
            'name' => $field
        ));

        // If doesn't exist create it on global
        if(!$sub_category){

            // Find or create the global category
            $global_category = $this->em->getRepository('ListBrokingAppBundle:Category')->findOneBy(array(
                'name' => 'global')
            );
            if(!$global_category){
                $global_category = new Category();
                $global_category->setName('global');
                $this->em->persist($global_category);

                $validations['infos'][$this->getName()][] = 'New Category created: ' .  $global_category->getName();
            }

            $sub_category = new SubCategory();
            $sub_category->setName($field);
            $sub_category->setCategory($global_category);

            $this->em->persist($sub_category);

            $validations['infos'][$this->getName()][] = 'New SubCategory created: ' .  $sub_category->getName();
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName(){
        return 'category_validator';
    }

}