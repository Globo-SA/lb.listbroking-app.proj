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
        $builder->add('upload_file', 'file', array(
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
