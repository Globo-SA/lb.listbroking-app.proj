<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LockType extends AbstractType
{
    /**
     * @var
     */
    private $name;
    /**
     * @var
     */
    private $label;
    /**
     * @var
     */
    private $choices;
    /**
     * @var
     */
    private $expiration_choices;

    function __construct($name, $label, $choices, $expiration_choices)
    {
        $this->name = $name;
        $this->label = $label;
        $this->choices = $choices;
        $this->expiration_choices = $expiration_choices;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->name, 'choice', array(
                'required' => false,
                'attr' => array(
                    'data-select-mode' => 'local',
                    'data-placeholder' => 'Select one or more...',
                    'class' => 'form-control'
                ),
                'label' => $this->label,
                'choices' => $this->choices
            ))
            ->add('interval', 'choice', array(
                'required' => false,

                'attr' => array(
                    'data-select-mode' => 'local',
                    'data-placeholder' => 'Select one...',
                    'class' => 'form-control'
                ),
                'label' => 'Expired',
                'choices' => $this->expiration_choices
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'filter';
    }
} 