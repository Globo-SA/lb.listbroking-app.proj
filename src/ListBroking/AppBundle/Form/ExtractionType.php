<?php

namespace ListBroking\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExtractionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('attr' => array('class' =>'form-control')))
            ->add('campaign', 'hidden', array(
                'data_class' => 'ListBroking\AppBundle\Entity\Campaign',
                'attr' => array(
                    'id' => $this->getName() . '_campaign',
                    'data-select-mode' => 'ajax',
                    'data-select-type' => 'campaign',
                    'data-select-parent' => '#' . $this->getName() . '_client',
                    'placeholder' => 'Campaigns list...',
                    'class' =>'form-control'
            )))
            ->add('status', 'choice', array(
                'attr' => array(
                    'data-select-mode' => 'local'
                ),
                'choices' => array(
                    0 => 'STATUS_CONFIGURATION',
                    1 => 'STATUS_FILTRATION',
                    2 => 'STATUS_CONFIRMATION',
                    3 => 'STATUS_FINAL'
                )
            ))
            ->add('quantity', null, array('attr' => array('class' =>'form-control')))
            ->add('payout', null, array('attr' => array('class' =>'form-control')))
        ;

        /* Exclude auto fields */
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event)
        {
            $form = $event->getForm();
            if ($event->getData())
            {
                $form
                    ->remove('name')
                    ->remove('campaign')
                    ->remove('status')
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
            'data_class' => 'ListBroking\AppBundle\Entity\Extraction',
            'allow_extra_fields' => true
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'extraction';
    }
}
