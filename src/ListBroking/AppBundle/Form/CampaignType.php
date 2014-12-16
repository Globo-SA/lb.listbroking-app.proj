<?php

namespace ListBroking\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CampaignType extends AbstractType
{

    private $uniqid;

    function __construct()
    {
        // An UniqueID is appended to the
        // name to avoid form collisions
        $this->uniqid = uniqid();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client', 'hidden', array(
                'data_class' => 'ListBroking\AppBundle\Entity\Client',
                'attr' => array(
                    'data-select-mode' => 'ajax',
                    'data-select-type' => 'client',
                    'placeholder' => 'Clients list...',
                    'class' =>'form-control'
            )))
            ->add('name', null, array('attr' => array('class' =>'form-control')))
            ->add('description', null, array('attr' => array('class' =>'form-control')))
        ;

        /* Exclude auto fields */
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $form = $event->getForm();
            if ($event->getData())
            {
                $form
                    ->add('created_at', null, array('label' => 'crud.global.form.form.created_at'))
                    ->add('updated_at', null, array('label' => 'crud.global.form.form.updated_at'))
                    ->add('created_by', null, array('label' => 'crud.global.form.form.created_by'))
                    ->add('updated_by', null, array('label' => 'crud.global.form.form.updated_by'));
            }
        });
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ListBroking\AppBundle\Entity\Campaign',
            'intention' => 'campaign'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'campaign_' . $this->uniqid;
    }
}