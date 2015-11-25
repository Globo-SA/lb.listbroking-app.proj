<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DataCardFilterType extends AbstractType
{

    const ENTITY_TYPE = 'entity';
    
    const AGGREGATION_TYPE = 'aggregation';

    const AVAILABILITY_TYPE = 'availability';
    
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm (FormBuilderInterface $builder, array $options)
    {
       $builder
           ->add('entity_country', 'entity', array(
               'class' => 'ListBroking\AppBundle\Entity\Country',
               'multiple' => true,
               'required' => false,
               'label' => 'Countries'
           ))
           ->add('entity_source', 'entity', array(
               'class' => 'ListBroking\AppBundle\Entity\Source',
               'multiple' => true,
               'required' => false,
               'label' => 'Source'
           ))
           ->add('entity_owner', 'entity', array(
               'class' => 'ListBroking\AppBundle\Entity\Owner',
               'multiple' => true,
               'required' => false,
               'label' => 'Owner'
           ))
           ->add('entity_sub_category', 'entity', array(
               'class' => 'ListBroking\AppBundle\Entity\SubCategory',
               'multiple' => true,
               'required' => false,
               'label' => 'SubCategory'
           ))

           ->add('availability_firstname', 'checkbox', array('label' => 'Has Firstname', 'required' => false))
           ->add('availability_birthdate', 'checkbox', array('label' => 'Has Birthdate', 'required' => false))
           ->add('availability_postalcode1', 'checkbox', array('label' => 'Has Postalcode1', 'required' => false))
           ->add('availability_postalcode2', 'checkbox', array('label' => 'Has Postalcode2', 'required' => false))
           ->add('availability_address', 'checkbox', array('label' => 'Has Address', 'required' => false))

           ->add('aggregation_country', 'checkbox', array('label' => 'Country', 'required' => false, 'data' => false))
           ->add('aggregation_source', 'checkbox', array('label' => 'Source', 'required' => false, 'data' => false))
           ->add('aggregation_owner', 'checkbox', array('label' => 'Owner', 'required' => false, 'data' => false))

           ->add('aggregation_district', 'checkbox', array('label' => 'District', 'required' => false, 'data' => false))
           ->add('aggregation_county', 'checkbox', array('label' => 'County', 'required' => false, 'data' => false))
           ->add('aggregation_parish', 'checkbox', array('label' => 'Parish', 'required' => false, 'data' => false))

           ->add('aggregation_postalcode1', 'checkbox', array('label' => 'Postalcode 1', 'required' => false, 'data' => false))
           ->add('aggregation_postalcode2', 'checkbox', array('label' => 'Postalcode 2', 'required' => false, 'data' => false))

           ->add('aggregation_gender', 'checkbox', array('label' => 'Gender', 'required' => false))
           ->add('aggregation_is_mobile', 'checkbox', array('label' => 'Phone Type (fixed/mobile)', 'required' => false))
           ->add('aggregation_sub_category', 'checkbox', array('label' => 'SubCategory', 'required' => false))
       ;

    }

    /**
     * @return string
     */
    public function getName ()
    {
        return 'data_card_filter';
    }
} 