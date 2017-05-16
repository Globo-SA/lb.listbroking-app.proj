<?php

namespace ListBroking\AppBundle\Form;

use ListBroking\AppBundle\Entity\Lock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ExtractionLockingType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder->add('lock_type', 'choice', array(
            "multiple" => true,
            "expanded" => true,
            "label"    => "List of items:",
            "choices"  => array(
                Lock::TYPE_CLIENT       => 'CLIENT',
                Lock::TYPE_CAMPAIGN     => 'CAMPAIGN',
                Lock::TYPE_CATEGORY     => 'CATEGORY',
                Lock::TYPE_SUB_CATEGORY => 'SUBCATEGORY'
            ),
        ))
                ->add('send_hurry', 'checkbox', array(
                    'label' => 'Send extraction to hurry',
                    'value' => true,
                    'data' => true,
                    'attr'              => array(
                        'class'       => 'form-control extra-lock-config'
                    )
        ))
        ;
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return 'extraction_locking';
    }
}
