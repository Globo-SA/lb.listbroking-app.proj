<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\UIBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CountryFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_active', 'choice', array(
                    'label' => 'ui.form.label.country.is_active',
                    'choices' => array(
                        'ui.form.text.global.false',
                        'ui.form.text.global.true'
                    )
                )
            )
            ->add('name', null, array('label' => 'ui.form.label.country.name'))
            ->add('iso_code', null, array('label' => 'ui.form.label.country.iso_code'))
            ->add('submit', 'submit')
        ;

        /* Exclude auto fields */
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
            {
                $form = $event->getForm();
                if (!$event->getData())
                {
                    $form
                        ->add('created_at', null, array('label' => 'ui.form.label.global.created_at'))
                        ->add('updated_at', null, array('label' => 'ui.form.label.global.updated_at'))
                        ->add('created_by', null, array('label' => 'ui.form.label.global.created_by'))
                        ->add('updated_by', null, array('label' => 'ui.form.label.global.updated_by'))
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
                'data_class' => 'ListBroking\CoreBundle\Entity\Country',
                'cascade_validation' => true,
                'csrf_protection'   => false,
                'error_bubbling'    => true,
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'country_form';
    }
}