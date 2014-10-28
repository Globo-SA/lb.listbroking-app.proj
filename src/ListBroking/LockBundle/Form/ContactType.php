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

class ContactType extends AbstractType
{

    private $uniqid;

    /**
     * @var
     */
    private $field;

    function __construct($field)
    {
        // An UniqueID is appended to the
        // name to avoid form collisions
        $this->uniqid = uniqid();

        $this->field = $field;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->field['name'], $this->field['type'], $this->field['options'])
            ->add('opt', 'choice', array(
                'label' => 'Operation',
                'choices' => array('equal' => 'Equal', 'not_equal' => 'Not Equal'),
                'attr' => array(
                    'class' => 'form-control',
                    'data-select-mode' => 'local',
                    'placeholder' => 'Select one...',
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