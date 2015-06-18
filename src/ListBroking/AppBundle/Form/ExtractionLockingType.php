<?php

namespace ListBroking\AppBundle\Form;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Lock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExtractionLockingType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $items = array(Lock::TYPE_CLIENT => 'CLIENT', Lock::TYPE_CAMPAIGN => 'CAMPAIGN', Lock::TYPE_CATEGORY => 'CATEGORY', Lock::TYPE_SUB_CATEGORY => 'SUBCATEGORY');
        $builder
            ->add('lock_type', 'choice', array(
                "multiple" => true,
                "expanded" => true,
                "label" => "List of items:",
                "choices" => $items
            ))
//            ->add('lock_type', 'checkbox', array(
//                'label' => 'CLIENT',
//                'value' => Lock::TYPE_CLIENT
//            ))
//            ->add('lock_type', 'checkbox', array(
//                'label' => 'CAMPAIGN',
//                'value' => Lock::TYPE_CAMPAIGN
//            ))
//            ->add('lock_type', 'checkbox', array(
//                'label' => 'CATEGORY',
//                'value' => Lock::TYPE_CATEGORY
//            ))
//            ->add('lock_type', 'checkbox', array(
//                'label' => 'SUBCATEGORY',
//                'value' => Lock::TYPE_SUB_CATEGORY
//            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'extraction_locking';
    }
}
