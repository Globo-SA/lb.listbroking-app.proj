<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RangeType extends AbstractType
{

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $type;

    /**
     * @var
     */
    private $label;

    /**
     * @var
     */
    private $default_value;

    function __construct ($name, $type, $label, $default_value = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->label = $label;
        $this->default_value = $default_value;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder->add($this->name, 'text', array(
            'label'    => $this->label,
            'required' => false,
            'attr'     => array(
                'data-toggle' => $this->type,
                'placeholder' => 'Select...',
                'class'       => 'form-control'
            ),
        ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            $this->name => "$this->default_value"
        ));
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return 'filter';
    }
} 