<?php

namespace ListBroking\AppBundle\Form;

use ListBroking\AppBundle\Entity\Extraction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ExtractionDeduplicationType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deduplication_type', 'choice', array(
                    'label' => 'Deduplication type',
                    'choices' => array(
                        Extraction::EXCLUDE_DEDUPLICATION_TYPE => 'File with the leads to remove',
                        Extraction::INCLUDE_DEDUPLICATION_TYPE => 'File with the leads to keep'
                    ),
                    'attr' => array(
                        'class' => 'form-control',
                        'data-select' => 'local'
                    )
                )
            )
            ->add('field', 'choice', array(
                    'label' => 'Deduplicate by',
                    'choices' => array('phone' => 'Phone'),
                    'attr' => array(
                        'class' => 'form-control',
                        'data-select' => 'local'
                    )
                )
            )
            ->add('upload_file', 'file', array(
                'attr' => array('class' => 'form-control fileinput')
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'extraction_deduplication';
    }
}
