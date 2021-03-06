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
                'choices'           => Extraction::$deduplication_names,
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
                ->add('remove_old_deduplication', 'checkbox', array(
                    'label' => 'Remove previous deduplications',
                    'value' => true,
                    'attr'              => array(
                        'class'       => 'form-control extra-dedup-config'
                    )
                ))
                ->add('skip_run_extraction', 'checkbox', array(
                    'label' => 'Skip extraction step',
                    'value' => true,
                    'attr'              => array(
                        'class'       => 'form-control extra-dedup-config'
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
