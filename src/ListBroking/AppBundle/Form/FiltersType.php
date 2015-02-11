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

use ListBroking\AppBundle\Form\Type\RangeType;
use ListBroking\AppBundle\Service\Helper\AppService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltersType extends AbstractType
{
    private $filters;

    /**
     * @var AppService
     */
    private $a_service;

    function __construct(AppService $appService)
    {
        $this->a_service = $appService;

        $default_country = $this->a_service->getCountryByCode('PT', false);
        $default_date = array(date('Y/m/01 - Y/m/t')); // Current month

        // Choice values arrays
        $genders = $this->a_service->getEntities('gender', false);
        $countries = $this->a_service->getEntities('country', false);
        $districts = $this->a_service->getEntities('district', false);
        $counties = $this->a_service->getEntities('county', false);
        $parishes = $this->a_service->getEntities('parish', false);
        $owners = $this->a_service->getEntities('owner', false);
        $sources = $this->a_service->getEntities('source', false);
        $categories = $this->a_service->getEntities('category', false);
        $sub_categories = $this->a_service->getEntities('sub_category', false);
        $clients = $this->a_service->getEntities('client', false);
        $campaigns = $this->a_service->getEntities('campaign', false);

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
                        'name' => 'contact:gender',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Gender',
                            'choices' => $this->getChoicesArray($genders)
                        )
                    ),
                    array(
                        'name' => 'contact:email',
                        'type' => 'hidden',
                        'options' => array(
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'open',
                                'placeholder' => 'Write and press enter...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Email',
                        )
                    ),
                    array(
                        'name' => 'contact:birthdate',
                        'type' => 'collection',
                        'options' => array(
                            'required' => false,
                            'attr' => array(
                                'data-collection' => 'true',
                                'class' => 'col-md-12'
                            ),
                            'type' => new RangeType('birthdate_range', 'birthdaterangepicker','Birthdate Range'),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'label' => 'Birthdate'
                        )
                    ),
                    array(
                        'name' => 'contact:date',
                        'type' => 'collection',
                        'options' => array(
                            'required' => false,
                            'empty_data' => array($default_date),
                            'attr' => array(
                                'data-collection' => 'true',
                                'class' => 'col-md-12'
                            ),
                            'type' => new RangeType('date', 'daterangepicker', 'Date Range'),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'label' => 'Contact date'
                        )
                    ),
                )
            ),
            'lead_details' => array(
                'label' => 'Lead Details',
                'fields' => array(
                    array(
                        'name' => 'lead:is_mobile',
                        'type' => 'checkbox',
                        'options' => array(
                            'required' => false,
                            'attr' => array(
                                'class' => 'form-control'
                            ),
                            'label' => 'Only mobile numbers'
                        )
                    ),
//                    array(
//                        'name' => 'lead:id',
//                        'type' => 'textarea',
//                        'options' => array(
//                            'required' => false,
//                            'attr' => array(
//                                'id' => 'filters_lead_details_lead_id',
//                                'placeholder' => 'Write and press enter...',
//                                'class' => 'form-control'
//                            ),
//                            'label' => 'Lead IDs to remove',
//                        )
//                    ),
//                    array(
//                        'name' => 'lead:phone',
//                        'type' => 'textarea',
//                        'options' => array(
//                            'required' => false,
//                            'attr' => array(
//                                'id' => 'filters_lead_details_lead_phone',
//                                'placeholder' => 'Write and press enter...',
//                                'class' => 'form-control'
//                            ),
//                            'label' => 'Lead phones to remove',
//                        )
//                    ),
                )
            ),
            "location" => array(
                'label' => 'Location',
                'fields' => array(
                    array(
                        'name' => 'contact:country',
                        'type' => 'choice',
                        'options' => array(
                            'empty_data' => array($default_country['id']),
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Country',
                            'choices' => $this->getChoicesArray($countries)
                        )
                    ),
                    array(
                        'name' => 'contact:district',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'District',
                            'choices' => $this->getChoicesArray($districts)
                        )
                    ),
                    array(
                        'name' => 'contact:county',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'County',
                            'choices' => $this->getChoicesArray($counties)
                        )
                    ),
                    array(
                        'name' => 'contact:parish',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Parish',
                            'choices' => $this->getChoicesArray($parishes)
                        )
                    ),
                    array(
                        'name' => 'contact:postalcode1',
                        'type' => 'hidden',
                        'options' => array(
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'open',
                                'placeholder' => 'Write and press enter...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Postalcode1',
                        )
                    ),
                    array(
                        'name' => 'contact:postalcode2',
                        'type' => 'hidden',
                        'options' => array(
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'open',
                                'placeholder' => 'Write and press enter...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Postalcode2',
                        )
                    ),
                ),
            ),
            "ownership_source_and_categorization" => array(
                'label' => 'Ownership, Source and Categorization',
                'fields' => array(
                    array(
                        'name' => 'contact:owner',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Owner',
                            'choices' => $this->getChoicesArray($owners)
                        )
                    ),
                    array(
                        'name' => 'contact:source',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Source',
                            'choices' => $this->getChoicesArray($sources)
                        )
                    ),
                    array(
                        'name' => 'contact:category',
                        'type' => 'choice',
                        'options' => array(
                            'disabled' => 'disabeld',
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'Category (TODO)',
                            'choices' => $this->getChoicesArray($categories)
                        )
                    ),
                    array(
                        'name' => 'contact:sub_category',
                        'type' => 'choice',
                        'options' => array(
                            'multiple' => true,
                            'required' => false,
                            'attr' => array(
                                'data-select-mode' => 'local',
                                'placeholder' => 'Select one or more...',
                                'class' => 'form-control'
                            ),
                            'label' => 'SubCategory',
                            'choices' => $this->getChoicesArray($sub_categories)
                        )
                    ),
                ),
            ),
            'basic_locks' => array(
                'label' => 'Basic Lead Locks',
                'fields' => array(
                    array(
                        'name' => 'lock:no_locks_lock_filter',
                        'type' => 'checkbox',
                        'options' => array(
                            'value' => 1, //NoLocksFilter
                            'data' => true,
                            'required' => false,
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
                        'name' => 'lock:reserved_lock_filter',
                        'type' => 'checkbox',
                        'options' => array(
                            'value' => 2, //ReservedLockType
                            'data' => true,
                            'required' => false,
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
                        'name' => 'lock:client_lock_filter',
                        'type' => 'collection',
                        'options' => array(
                            'required' => false,
                            'attr' => array(
                                'data-collection' => 'true',
                                'class' => 'col-md-12'
                            ),
                            'type' => new LockType(
                                        'client',
                                        'Client',
                                        $this->getChoicesArray($clients),
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
                        'name' => 'lock:campaign_lock_filter',
                        'type' => 'collection',
                        'options' => array(
                            'required' => false,
                            'attr' => array(
                                'data-collection' => 'true',
                                'class' => 'col-md-12'
                            ),
                            'type' => new LockType(
                                        'campaign',
                                        'Campaign',
                                        $this->getChoicesArray($campaigns),
                                        $expiration_choices),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'label' => 'Client'
                        )
                    ),
                ),
            ),
            'category_locks' => array(
                'label' => 'Category Locks',
                'fields' => array(
                    array(
                        'name' => 'lock:category_lock_filter',
                        'type' => 'collection',
                        'required' => false,
                        'options' => array(
                            'attr' => array(
                                'data-collection' => 'true',
                                'class' => 'col-md-12'
                            ),
                            'type' => new LockType(
                                        'category',
                                        'Category',
                                        $this->getChoicesArray($categories),
                                        $expiration_choices),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'label' => 'Category'
                        )
                    ),
                ),
            ),
            'sub_category_locks' => array(
                'label' => 'SubCategory Locks',
                'fields' => array(
                    array(
                        'name' => 'lock:sub_category_lock_filter',
                        'type' => 'collection',
                        'required' => false,
                        'options' => array(
                            'attr' => array(
                                'data-collection' => 'true',
                                'class' => 'col-md-12'
                            ),
                            'type' => new LockType(
                                        'sub_category',
                                        'SubCategory',
                                        $this->getChoicesArray($sub_categories),
                                        $expiration_choices),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'label' => 'SubCategory'
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
                'label_attr' => array('class' => 'text-blue'),
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
        $choices = array();
        foreach ($list as $choice)
        {
            if (array_key_exists('id', $choice) && array_key_exists('name', $choice))
                $choices[$choice['id']] = $choice['name'];
        }

        return $choices;
    }
}
