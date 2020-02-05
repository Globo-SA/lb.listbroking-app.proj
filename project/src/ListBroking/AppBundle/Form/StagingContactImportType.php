<?php

namespace ListBroking\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class StagingContactImportType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('owner', 'entity', array(
                'class' => 'ListBroking\AppBundle\Entity\Owner',
                'property' => 'name'
            ))
            ->add('update', 'checkbox', array('required' => false, 'data' => true, 'label' => 'Update existing contacts ?'))
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
        return 'staging_contact_import';
    }
}
