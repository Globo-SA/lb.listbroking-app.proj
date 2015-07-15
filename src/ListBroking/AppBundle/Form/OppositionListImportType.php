<?php

namespace ListBroking\AppBundle\Form;

use ListBroking\AppBundle\Service\Helper\AppService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OppositionListImportType extends AbstractType
{

    /**
     * @var AppService
     */
    private $service;

    function __construct(AppService $service)
    {
        $this->service = $service;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $choices = json_decode($this->service->findConfig('opposition_list.types'), 1);
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
