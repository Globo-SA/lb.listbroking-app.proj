<?php

namespace ListBroking\AppBundle\Form;

use ListBroking\AppBundle\Service\Helper\AppServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OppositionListImportType extends AbstractType
{

    /**
     * @var AppServiceInterface
     */
    private $service;

    function __construct(AppServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $choices = $this->service->findConfig('opposition_list.types');
        $builder
            ->add('type', 'choice', array(
                    'label' => 'List type',
                    'choices' => $choices,
                    'attr' => array(
                        'class' => 'form-control',
                        'data-select' => 'local'
                    )
                )
            )
            ->add('upload_file', 'file', array(
                'attr' => array('class' => 'form-control fileinput')
            ))
            ->add('clear_old', 'checkbox', array('required' => false, 'data' => true))
        ;
    }



    /**
     * @return string
     */
    public function getName()
    {
        return 'opposition_list_import';
    }
}
