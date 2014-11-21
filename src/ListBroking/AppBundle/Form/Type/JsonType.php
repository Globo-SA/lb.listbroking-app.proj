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


use ListBroking\AppBundle\Form\DataTransformer\ArrayToFormTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JsonType extends AbstractType {


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        foreach ($options['fields'] as $field){
            $builder->add($field['name'], $field['type'], $field['options']);
        }
        $builder->addModelTransformer(new ArrayToFormTransformer());


    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => true,
            'fields' => array(
                array(
                    'name' => 'stuff_selector',
                    'type' => 'choice',
                    'options' => array(
                        'choices' => array(
                            1 => 'M',
                            2 => 'F'
                        ),
                        'label' => 'Cool Stuff'
                    )
                ),
                array(
                    'name' => 'stuff',
                    'type' => 'text',
                    'options' => array(
                        'label' => 'Cool Stuff'
                    )
                )
            )
        ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'json';
    }
} 