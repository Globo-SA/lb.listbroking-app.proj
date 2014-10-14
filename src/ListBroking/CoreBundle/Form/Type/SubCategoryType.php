<?php

namespace ListBroking\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SubCategoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_active')
            ->add('name')
            ->add('created_at')
            ->add('updated_at')
            ->add('created_by')
            ->add('updated_by')
            ->add('category')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ListBroking\CoreBundle\Entity\SubCategory'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'subcategory_form';
    }
}