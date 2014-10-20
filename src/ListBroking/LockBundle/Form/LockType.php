<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LockType extends AbstractType
{

    private $uniqid;
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
        // An UniqueID is appended to the
        // name to avoid form collisions
        $this->uniqid = uniqid();

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
                'multiple' => true,
                'attr' => array(
                    'data-select-mode' => 'local',
                    'placeholder' => 'Select one or more...',
                    'class' => 'form-control'
                ),
                'label' => $this->label,
                'choices' => $this->choices
            ))
            ->add('expiration_date', 'choice', array(
                'attr' => array(
                    'data-select-mode' => 'local',
                    'placeholder' => 'Select one...',
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
//        return 'filter_' . $this->uniqid;
    }
} 