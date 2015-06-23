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
                "multiple"          => true,
                "expanded"          => true,
                "label"             => "List of items:",
                "choices"           => array(
                    'CLIENT'      => Lock::TYPE_CLIENT,
                    'CAMPAIGN'    => Lock::TYPE_CAMPAIGN,
                    'CATEGORY'    => Lock::TYPE_CATEGORY,
                    'SUBCATEGORY' => Lock::TYPE_SUB_CATEGORY
                ),
                'choices_as_values' => true,
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
