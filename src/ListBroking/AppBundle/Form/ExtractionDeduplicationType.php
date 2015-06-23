<?php

namespace ListBroking\AppBundle\Form;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ExtractionDeduplicationType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder->add('deduplication_type', 'choice', array(
                'label'             => 'Deduplication type',
                'choices'           => array(
                    'File with the leads to remove' => Extraction::EXCLUDE_DEDUPLICATION_TYPE,
                    'File with the leads to keep'   => Extraction::INCLUDE_DEDUPLICATION_TYPE
                ),
                'choices_as_values' => true,
                'attr'              => array(
                    'class'       => 'form-control',
                    'data-select' => 'local'
                )
            ))
                ->add('field', 'choice', array(
                    'label'             => 'Deduplicate by',
                    'choices'           => array(
                        'Phone' => ExtractionDeduplication::TYPE_PHONE
                    ),
                    'choices_as_values' => true,
                    'attr'              => array(
                        'class'       => 'form-control',
                        'data-select' => 'local'
                    )
                ))
                ->add('upload_file', 'file', array(
                    'attr' => array('class' => 'form-control fileinput')
                ))
        ;
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return 'extraction_deduplication';
    }
}
