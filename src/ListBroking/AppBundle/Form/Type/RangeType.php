<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RangeType extends AbstractType
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

    function __construct($name, $label)
    {
        // An UniqueID is appended to the
        // name to avoid form collisions
        $this->uniqid = uniqid();

        $this->name = $name;
        $this->label = $label;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->name, 'text', array(
                'label' => $this->label,
                'required' => false,
                'attr' => array(
                    'data-toggle' => 'daterangepicker',
                    'placeholder' => 'Select one...',
                    'class' => 'form-control'
                    )
                )
            )
        ;
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