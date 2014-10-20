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


use ListBroking\ClientBundle\Service\ClientService;
use ListBroking\CoreBundle\Service\CoreService;
use ListBroking\LeadBundle\Service\ContactDetailsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltersForm extends AbstractType
{

    private $filters;
    /**
     * @var CoreService
     */
    private $c_service;
    /**
     * @var ContactDetailsService
     */
    private $cd_service;
    /**
     * @var ClientService
     */
    private $cl_service;

    function __construct(CoreService $c_service, ContactDetailsService $cd_service, ClientService $cl_service)
    {
        $this->c_service = $c_service;
        $this->cd_service = $cd_service;
        $this->cl_service = $cl_service;

        $expiration_choices = array(
          '' => '',
          '-1 week' => '1 Week ago',
          '-2 week' => '2 Weeks ago',
          '-3 week' => '3 Weeks ago',
          '-1 month' => '1 month ago',
          '-2 month' => '2 months ago',
          '-3 month' => '3 months ago',
          '-4 month' => '4 months ago',
          '-5 month' => '5 months ago',
          '-6 month' => '6 months ago',

        );
        $this->filters = array(
            "contact_details" => array(
                'label' => 'Contact Details',
                'fields' => array(
                    array(
                        'name' => 'gender',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Gender',
                            'choices' => $this->getChoicesArray($this->cd_service->getGenderList())
                        )
                    ),
                    array(
                        'name' => 'birthdate_range',
                        'type' => 'text',
                        'options' => array(
                            'attr' => array(
                                'data-toggle' => 'daterangepicker',
                                'placeholder' => 'Select one...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Birthdate Range',
                        )
                    ),
                    array(
                        'name' => 'email',
                        'type' => 'email',
                        'options' => array(
                            'attr' => array(
                                'placeholder' => 'Select one...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Email',
                        )
                    ),
                ),
            ),
            "ownership_source_and_categorization" => array(
                'label' => 'Ownership, Source and Categorization',
                'fields' => array(
                    array(
                        'name' => 'owner',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Owner',
                            'choices' => $this->getChoicesArray($this->cd_service->getOwnerList())
                        )
                    ),
                    array(
                        'name' => 'source',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Source',
                            'choices' => $this->getChoicesArray($this->cd_service->getSourceList())
                        )
                    ),
                    array(
                        'name' => 'category',
                        'type' => 'choice',
                        'options' => array(
                            'disabled' => 'disabeld',
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Category (TODO)',
                            'choices' => $this->getChoicesArray($this->c_service->getCategoryList())
                        )
                    ),
                    array(
                        'name' => 'sub_category',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'SubCategory',
                            'choices' => $this->getChoicesArray($this->c_service->getSubCategoryList())
                        )
                    ),
                ),
            ),
            "location" => array(
                'label' => 'Location',
                'fields' => array(
                    array(
                        'name' => 'country',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Country',
                            'choices' => $this->getChoicesArray($this->c_service->getCountryList())
                        )
                    ),
                    array(
                        'name' => 'district',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'District',
                            'choices' => $this->getChoicesArray($this->cd_service->getDistrictList())
                        )
                    ),
                    array(
                        'name' => 'county',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'County',
                            'choices' => $this->getChoicesArray($this->cd_service->getCountyList())
                        )
                    ),
                    array(
                        'name' => 'parish',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Parish',
                            'choices' => $this->getChoicesArray($this->cd_service->getParishList())
                        )
                    ),
                    array(
                        'name' => 'postalcode1',
                        'type' => 'text',
                        'options' => array(
                            'attr' => array(
                                'data-multiple-range' => true,
                                'placeholder' => 'Choose one or more range...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Postalcode 1',
                        )
                    ),
                    array(
                        'name' => 'postalcode2',
                        'type' => 'text',
                        'options' => array(
                            'attr' => array(
                                'data-multiple-range' => true,
                                'placeholder' => 'Choose one or more ranges...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Postalcode 2',
                        )
                    ),
                ),
            ),
            'basic_locks' => array(
                'label' => 'Basic Lead Locks',
                'fields' => array(
                    array(
                        'name' => 'not_locked',
                        'type' => 'checkbox',
                        'options' => array(
                            'value' => 1, //NoLocksFilter
                            'data' => true,
                            'attr' => array(
                                'data-toggle' => 'tooltip',
                                'data-trigger' => 'hover',
                                'data-placement' => 'top',
                                'title' => 'Only select leads without active locks, this should always be checked!',
                                'class' => 'form-control'
                            ),
                            'label' => 'Only unlocked Leads'
                        )
                    ),
                    array(
                        'name' => 'not_reserved',
                        'type' => 'checkbox',
                        'options' => array(
                            'value' => 2, //ReservedLockType
                            'data' => true,
                            'attr' => array(
                                'data-toggle' => 'tooltip',
                                'data-trigger' => 'hover',
                                'data-placement' => 'top',
                                'title' => 'Only Select leads that aren\'t reserved on an other extraction',
                                'class' => 'form-control'
                            ),
                            'label' => 'Only free Leads'
                        )
                    ),
                )
            ),
            'client_locks' => array(
                'label' => 'Client Locks',
                'fields' => array(
                    array(
                        'name' => 'not_client_lock',
                        'type' => 'collection',
                        'options' => array(
                            'attr' => array(
                                'data-collection' => 'true',
                                'class' => 'col-md-12'
                            ),
                            'type' => new LockType(
                                        'client',
                                        'Client',
                                        $this->getChoicesArray($this->cl_service->getClientList()),
                                        $expiration_choices),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'label' => 'Client'
                        )
                    ),
                ),
            ),
            'campaign_locks' => array(
                'label' => 'Campaign Locks',
                'fields' => array(
                    array(
                        'name' => 'not_campaign_lock',
                        'type' => 'collection',
                        'options' => array(
                            'attr' => array(
                                'data-collection' => 'true',
                                'class' => 'col-md-12'
                            ),
                            'type' => new LockType(
                                        'campaign',
                                        'Campaign',
                                        $this->getChoicesArray($this->cl_service->getCampaignList()),
                                        $expiration_choices),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'label' => 'Client'
                        )
                    ),
                ),
            )
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->filters as $group_name => $group)
        {
            $virtual_form = $builder->create($group_name, 'form', array(
                'label' => $group['label'],
                'virtual' => true,
                'attr' => array('class' => 'row')
            ));
            foreach ($group['fields'] as $filter)
            {
                // Filters should always be optional
                if (!array_key_exists('required', $filter['options']))
                {
                    $filter['options']['required'] = false;
                }
                $virtual_form->add($filter['name'], $filter['type'], $filter['options']);
            }
            $builder->add($virtual_form);

        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'filters';
    }

    private function getChoicesArray($list)
    {

        //$choices = array('' => '-- Select one --');
        $choices = array();
        foreach ($list as $choice)
        {
            if (array_key_exists('id', $choice) && array_key_exists('name', $choice))
                $choices[$choice['id']] = $choice['name'];
        }

        return $choices;
    }
}
