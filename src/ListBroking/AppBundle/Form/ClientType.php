<?php

namespace ListBroking\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientType extends AbstractType
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
            ->add('name')
            ->add('account_name')
            ->add('phone')
            ->add('email_address')
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
                    ->add('updated_by', null, array('label' => 'crud.global.form.form.updated_by'))
                ;
            }
        });
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ListBroking\AppBundle\Entity\Client',
            'intention' => 'client'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'client_' . $this->uniqid;
    }
}
