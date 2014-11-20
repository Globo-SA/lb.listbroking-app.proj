<?php

namespace ListBroking\ExtractionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExtractionType extends AbstractType
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
            ->add('is_active', 'checkbox', array('data' => true))
            ->add('name')
            ->add('campaign', null, array('attr' => array(
                "id" => $this->getName() . '_campaign',
                "data-select-mode" => "ajax",
                "data-select-type" => "campaign",
                "data-select-parent" => "#" . $this->getName() . '_client',
                "placeholder" => "Campaigns list...",
                "class" =>"form-control"
            )))
            ->add('status', 'choice', array(
                'attr' => array(
                    "data-select-mode" => "local"
                ),
                'choices' => array(
                    0 => 'STATUS_CONFIGURATION',
                    1 => 'STATUS_FILTRATION',
                    2 => 'STATUS_CONFIRMATION',
                    3 => 'STATUS_FINAL'
                )
            ))
            ->add('quantity')
            ->add('payout')
        ;

        /* Exclude auto fields */
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event)
        {
            $form = $event->getForm();
            if ($event->getData())
            {
                $form
                    ->remove('is_active')
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
            'data_class' => 'ListBroking\ExtractionBundle\Entity\Extraction',
            'allow_extra_fields' => true
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'extraction_' . $this->uniqid;
    }
}
