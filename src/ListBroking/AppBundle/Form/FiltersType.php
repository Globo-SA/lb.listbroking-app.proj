<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Form;

use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\Filter\ContactCampaignFilterInterface;
use ListBroking\AppBundle\Engine\Filter\ContactFilterInterface;
use ListBroking\AppBundle\Engine\Filter\LeadFilterInterface;
use ListBroking\AppBundle\Engine\Filter\LockFilterInterface;
use ListBroking\AppBundle\Enum\ConditionOperatorEnum;
use ListBroking\AppBundle\Enum\FormFieldTypeEnum;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Form\Type\RangeType;
use ListBroking\AppBundle\Service\Helper\AppServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FiltersType extends AbstractType
{

    // Filter Types
    const INCLUSION_FILTER = 'inclusion';

    const EXCLUSION_FILTER = 'exclusion';

    private $filters;

    /**
     * @var AppServiceInterface
     */
    private $a_service;

    function __construct(AppServiceInterface $appService)
    {
        $this->a_service = $appService;

        // Default Values
        $default_date_range = date('Y/m/01 - Y/m/t'); // Current month
        $lock_time          = str_replace('+', '', $appService->findConfig('lock.time'));

        // Arrays for Choices
        $countries          = $this->a_service->findEntities('ListBrokingAppBundle:Country');
        $genders            = $this->a_service->findEntities('ListBrokingAppBundle:Gender');
        $owners             = $this->a_service->findEntities('ListBrokingAppBundle:Owner');
        $sources            = $this->a_service->findEntities('ListBrokingAppBundle:Source');
        $categories         = $this->a_service->findEntities('ListBrokingAppBundle:Category');
        $sub_categories     = $this->a_service->findEntities('ListBrokingAppBundle:SubCategory');
        $clients            = $this->a_service->findEntities('ListBrokingAppBundle:Client');
        $campaigns          = $this->a_service->findEntities('ListBrokingAppBundle:Campaign');
        $expiration_choices = [
            ''             => '',
            '3 months ago' => '-3 month',
            '4 months ago' => '-4 month',
            '5 months ago' => '-5 month',
            '6 months ago' => '-6 month',
            '1 year ago'   => '-1 year',
            '2 years ago'  => '-2 year',
            'Never sold'   => '-30 year',
        ];

        // Filter Schema
        $this->filters = [
            'required'                            => [
                'label'  => 'Required Fields',
                'attr'   => ['class' => 'in'],
                'fields' => [
                    [
                        'filter_type'          => ContactFilterInterface::REQUIRED_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'gender',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_BOOLEAN,
                        'type'                 => 'checkbox',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                            'label'    => 'Has Gender',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::REQUIRED_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'firstname',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_BOOLEAN,
                        'type'                 => 'checkbox',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                            'label'    => 'Has Firstname',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::REQUIRED_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'lastname',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_BOOLEAN,
                        'type'                 => 'checkbox',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                            'label'    => 'Has Lastname',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::REQUIRED_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'birthdate',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_BOOLEAN,
                        'type'                 => 'checkbox',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                            'label'    => 'Has Birthdate',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::REQUIRED_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'address',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_BOOLEAN,
                        'type'                 => 'checkbox',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                            'label'    => 'Has address',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::REQUIRED_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'postalcode1',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_BOOLEAN,
                        'type'                 => 'checkbox',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                            'label'    => 'Has Postalcode1',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::REQUIRED_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'postalcode2',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_BOOLEAN,
                        'type'                 => 'checkbox',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                            'label'    => 'Has Postalcode2',
                        ],
                    ],
                ],
            ],
            'contact'                             => [
                'label'  => 'Contact Details',
                'attr'   => ['class' => 'in'],
                'fields' => [
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'gender',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'choice',
                        'options'              => [
                            'multiple'          => true,
                            'required'          => false,
                            'attr'              => [
                                'data-select-mode' => 'local',
                                'placeholder'      => 'Select one or more...',
                                'class'            => 'form-control',
                            ],
                            'label'             => 'Genders',
                            'choices'           => $this->getChoicesArray($genders),
                            'choices_as_values' => true,
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'birthdate',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_RANGE,
                        'type'                 => 'collection',
                        'options'              => [
                            'required'     => false,
                            'attr'         => [
                                'data-collection' => 'true',
                                'class'           => 'col-md-12 blocked-input',
                            ],
                            'type'         => new RangeType('birthdate', 'birthdaterangepicker', 'Birthdate (range)'),
                            'allow_add'    => true,
                            'allow_delete' => true,
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'date',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_RANGE,
                        'type'                 => 'collection',
                        'options'              => [
                            'required'     => false,
                            'attr'         => [
                                'data-collection' => 'true',
                                'class'           => 'col-md-12 blocked-input',
                            ],
                            'type'         => new RangeType(
                                'date',
                                'daterangepicker',
                                'Contact acquisition dates (ranges)',
                                $default_date_range
                            ),
                            'allow_add'    => true,
                            'allow_delete' => true,
                        ],
                    ],
                ],
            ],
            'lead'                                => [
                'label'  => 'Lead Details',
                'attr'   => ['class' => 'in'],
                'fields' => [
                    [
                        'filter_type'          => LeadFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'lead',
                        'field'                => 'is_mobile',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_CHOICE,
                        'type'                 => 'choice',
                        'options'              => [
                            'placeholder' => false,
                            'choices'     => [
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_BOTH => 'Both',
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_YES  => 'Yes',
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_NO   => 'No',
                            ],
                            'attr'        => [
                                'class' => 'form-control',
                            ],
                            'label'       => 'Mobile numbers',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'is_clean',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_CHOICE,
                        'type'                 => 'choice',
                        'options'              => [
                            'placeholder' => false,
                            'choices'     => [
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_YES  => 'Yes',
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_NO   => 'No',
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_BOTH => 'Both',
                            ],
                            'attr'        => [
                                'class' => 'form-control',
                            ],
                            'label'       => 'Clean contacts',
                        ],
                    ],
                    [
                        'filter_type'          => LeadFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'lead',
                        'field'                => 'is_ready_to_use',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_CHOICE,
                        'type'                 => 'choice',
                        'options'              => [
                            'placeholder' => false,
                            'choices'     => [
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_YES  => 'Yes',
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_NO   => 'No',
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_BOTH => 'Both',
                            ],
                            'attr'        => [
                                'class' => 'form-control',
                            ],
                            'label'       => 'Contacts ready to be used',
                        ],
                    ],
                    [
                        'filter_type'          => LeadFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'lead',
                        'field'                => 'is_sms_ok',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_CHOICE,
                        'type'                 => 'choice',
                        'options'              => [
                            'placeholder' => false,
                            'choices'     => [
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_BOTH => 'Both',
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_YES  => 'Yes',
                                FormFieldTypeEnum::FIELD_TYPE_CHOICE_NO   => 'No',
                            ],
                            'attr'        => [
                                'class' => 'form-control',
                            ],
                            'label'       => 'Is Sms Ok',
                        ],
                    ],
                ],
            ],
            'location'                            => [
                'label'  => 'Location',
                'attr'   => ['class' => 'in'],
                'fields' => [
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact',
                        'field'                => 'country',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_INTEGER,
                        'type'                 => 'choice',
                        'options'              => [
                            'placeholder'       => false,
                            'choices'           => $this->getChoicesArray($countries),
                            'choices_as_values' => true,
                            'attr'              => [
                                'data-select-mode' => 'local',
                                'placeholder'      => 'Select one or more...',
                                'class'            => 'form-control',
                            ],
                            'label'             => 'Country',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'district',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'hidden',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'data-select-mode'          => 'ajax',
                                'data-select-minimum-input' => 2,
                                'data-select-multiple'      => true,
                                'data-select-type'          => 'District',
                                'placeholder'               => 'Select one or more...',
                                'class'                     => 'form-control',
                            ],
                            'label'    => 'Districts',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'county',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'hidden',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'data-select-mode'          => 'ajax',
                                'data-select-minimum-input' => 2,
                                'data-select-multiple'      => true,
                                'data-select-type'          => 'County',
                                'placeholder'               => 'Select one or more...',
                                'class'                     => 'form-control',
                            ],
                            'label'    => 'Counties',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'parish',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'hidden',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'data-select-mode'          => 'ajax',
                                'data-select-minimum-input' => 2,
                                'data-select-multiple'      => true,
                                'data-select-type'          => 'Parish',
                                'placeholder'               => 'Select one or more...',
                                'class'                     => 'form-control',
                            ],
                            'label'    => 'Parish',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'postalcode1',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_INTEGER,
                        'type'                 => 'hidden',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'data-select-mode' => 'open',
                                'placeholder'      => 'Write and press enter...',
                                'class'            => 'form-control',
                            ],
                            'label'    => 'Postalcode1 (ex: 4000-5000, 4000)',
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'postalcode2',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_INTEGER,
                        'type'                 => 'hidden',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'data-select-mode' => 'open',
                                'placeholder'      => 'Write and press enter...',
                                'class'            => 'form-control',
                            ],
                            'label'    => 'Postalcode2',
                        ],
                    ],
                ],
            ],
            'ownership_source_and_categorization' => [
                'label'  => 'Ownership, Source and Categorization',
                'attr'   => ['class' => 'in'],
                'fields' => [
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'owner',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'choice',
                        'options'              => [
                            'required'          => false,
                            'multiple'          => 'multiple',
                            'attr'              => [
                                'data-select-mode' => 'local',
                                'placeholder'      => 'Select one or more...',
                                'class'            => 'form-control',
                            ],
                            'label'             => 'Owners',
                            'choices'           => $this->getChoicesArray($owners),
                            'choices_as_values' => true,
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'source',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'choice',
                        'options'              => [
                            'required'          => false,
                            'multiple'          => 'multiple',
                            'attr'              => [
                                'data-select-mode' => 'local',
                                'placeholder'      => 'Select one or more...',
                                'class'            => 'form-control',
                            ],
                            'label'             => 'Sources',
                            'choices'           => $this->getChoicesArray($sources),
                            'choices_as_values' => true,
                        ],
                    ],
                    [
                        'filter_type'          => ContactFilterInterface::BASIC_TYPE,
                        'has_exclusion_filter' => true,
                        'table'                => 'contact',
                        'field'                => 'sub_category',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'choice',
                        'options'              => [
                            'required'          => false,
                            'multiple'          => 'multiple',
                            'attr'              => [
                                'data-select-mode' => 'local',
                                'placeholder'      => 'Select one or more...',
                                'class'            => 'form-control',
                            ],
                            'label'             => 'SubCategories',
                            'choices'           => $this->getChoicesArray($sub_categories),
                            'choices_as_values' => true,
                        ],
                    ],
                ],
            ],
            'sold_history'                        => [
                'label'  => 'Contact sold history',
                'attr'   => ['class' => 'in'],
                'fields' => [
                    [
                        'filter_type'          => ContactCampaignFilterInterface::NOT_SOLD_MORE_THAN_X_TIMES_AFTER_DATE_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact_campaign',
                        'field'                => 'max_times_sold',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_INTEGER,
                        'type'                 => 'integer',
                        'options'              => [
                            'required' => false,
                            'label'    => 'Max times sold',
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                        ],
                    ],
                    [
                        'filter_type'          => ContactCampaignFilterInterface::NOT_SOLD_MORE_THAN_X_TIMES_AFTER_DATE_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'contact_campaign',
                        'field'                => 'created_at',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_DATE_GREATER_THAN,
                        'type'                 => 'text',
                        'options'              => [
                            'required' => false,
                            'label'    => 'Since date',
                            'attr'     => [
                                'data-toggle' => 'datepicker',
                                'placeholder' => 'Select...',
                                'class'       => 'form-control',
                            ],
                        ],
                    ],
                ],

            ],
            'basic_locks'                         => [
                'label'  => 'Basic Lead Locks',
                'attr'   => ['class' => 'in'],
                'fields' => [
                    [
                        'filter_type'          => LockFilterInterface::NO_LOCKS_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'lock',
                        'field'                => 'no_locks_lock_filter',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_BOOLEAN,
                        'type'                 => 'checkbox',
                        'options'              => [
                            'required' => false,
                            'attr'     => [
                                'class' => 'form-control',
                            ],
                            'label'    => sprintf('Not sold in the last %s', $lock_time),
                        ],
                    ],
                ],
            ],
            'client_locks'                        => [
                'label'  => 'Client Locks',
                'attr'   => [],
                'fields' => [
                    [
                        'filter_type'          => LockFilterInterface::CLIENT_LOCK_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'lock',
                        'field'                => 'client_lock_filter',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'collection',
                        'options'              => [
                            'required'     => false,
                            'attr'         => [
                                'data-collection' => 'true',
                                'class'           => 'col-md-12',
                            ],
                            'type'         => new LockType(
                                'client',
                                'Client',
                                $this->getChoicesArray($clients),
                                $expiration_choices
                            ),
                            'allow_add'    => true,
                            'allow_delete' => true,
                            'label'        => 'Client',
                        ],
                    ],
                ],
            ],
            'campaign_locks'                      => [
                'label'  => 'Campaign Locks',
                'attr'   => [],
                'fields' => [
                    [

                        'filter_type'          => LockFilterInterface::CAMPAIGN_LOCK_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'lock',
                        'field'                => 'campaign_lock_filter',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'collection',
                        'options'              => [
                            'required'     => false,
                            'attr'         => [
                                'data-collection' => 'true',
                                'class'           => 'col-md-12',
                            ],
                            'type'         => new LockType(
                                'campaign',
                                'Campaign',
                                $this->getChoicesArray($campaigns),
                                $expiration_choices
                            ),
                            'allow_add'    => true,
                            'allow_delete' => true,
                            'label'        => 'Client',
                        ],
                    ],
                ],
            ],
            'category_locks'                      => [
                'label'  => 'Category Locks',
                'attr'   => [],
                'fields' => [
                    [
                        'filter_type'          => LockFilterInterface::CATEGORY_LOCK_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'lock',
                        'field'                => 'category_lock_filter',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'collection',
                        'required'             => false,
                        'options'              => [
                            'attr'         => [
                                'data-collection' => 'true',
                                'class'           => 'col-md-12',
                            ],
                            'type'         => new LockType(
                                'category',
                                'Category',
                                $this->getChoicesArray($categories),
                                $expiration_choices
                            ),
                            'allow_add'    => true,
                            'allow_delete' => true,
                            'label'        => 'Category',
                        ],
                    ],
                ],
            ],
            'sub_category_locks'                  => [
                'label'  => 'SubCategory Locks',
                'attr'   => [],
                'fields' => [
                    [
                        'filter_type'          => LockFilterInterface::SUB_CATEGORY_LOCK_TYPE,
                        'has_exclusion_filter' => false,
                        'table'                => 'lock',
                        'field'                => 'sub_category_lock_filter',
                        'field_type'           => FormFieldTypeEnum::FIELD_TYPE_ARRAY,
                        'type'                 => 'collection',
                        'required'             => false,
                        'options'              => [
                            'attr'         => [
                                'data-collection' => 'true',
                                'class'           => 'col-md-12',
                            ],
                            'type'         => new LockType(
                                'sub_category',
                                'SubCategory',
                                $this->getChoicesArray($sub_categories),
                                $expiration_choices
                            ),
                            'allow_add'    => true,
                            'allow_delete' => true,
                            'label'        => 'SubCategory',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Generates a choice array using a list of values
     *
     * @param $list
     *
     * @return array
     */
    private function getChoicesArray($list)
    {
        $choices = [];
        foreach ($list as $choice) {
            if (array_key_exists('id', $choice) && array_key_exists('name', $choice)) {
                $choices[$choice['name']] = $choice['id'];
            }
        }

        return $choices;
    }

    /**
     * Validates a given Filter
     *
     * @param $filter
     *
     * @throws InvalidFilterObjectException
     */
    public static function validateFilter($filter)
    {
        // Validate filter array
        if (!array_key_exists('filter_type', $filter)
            || !array_key_exists('filter_operation', $filter)
            || !array_key_exists('field', $filter)
            || !array_key_exists('field_type', $filter)
            || !array_key_exists('opt', $filter)
            || !array_key_exists('value', $filter)
        ) {
            throw new InvalidFilterObjectException(
                'Invalid filter, must be: array(\'filter_type\' => \'\',\'filter_operation\' => \'\',\'field\' => \'\',\'field_type\' => \'\', \'opt\' => \'\',
                    \'value\' => array()),
                    in ' . __CLASS__
            );
        }
    }

    /**
     * Generates and array of human-readable filters
     *
     * @param EntityManager $em
     * @param               $filters_string
     *
     * @return array
     */
    public static function humanizeFilters(EntityManager $em, $filters_string)
    {
        $filters_array = self::prepareFilters($filters_string);

        $final_filters = [];
        foreach ($filters_array as $type => $filters) {
            foreach ($filters as $filter) {
                foreach ($filter as $data) {
                    $final_values = [];
                    if (!is_array($data['value'])) {
                        $data['value'] = [$data['value']];
                    }

                    foreach ($data['value'] as $value) {
                        if (is_bool($value)) {
                            $final_values[] = $value ? 'TRUE' : 'FALSE';
                            continue;
                        }

                        if (is_array($value)) {
                            $final_values[] = print_r($value, 1);
                        }

                        if ($data['filter_type'] == ContactFilterInterface::BASIC_TYPE) {
                            switch ($data['field']) {
                                case 'source':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Source')
                                                         ->find($value)
                                                         ->getName();
                                    break;
                                case 'owner':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Owner')
                                                         ->find($value)
                                                         ->getName();
                                    break;
                                case 'sub_category':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:SubCategory')
                                                         ->find($value)
                                                         ->getName();
                                    break;
                                case 'gender':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Gender')
                                                         ->find($value)
                                                         ->getName();
                                    break;
                                case 'district':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:District')
                                                         ->find($value)
                                                         ->getName();
                                    break;
                                case 'county':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:County')
                                                         ->find($value)
                                                         ->getName();
                                    break;
                                case 'parish':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Parish')
                                                         ->find($value)
                                                         ->getName();
                                    break;
                                case 'country':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Country')
                                                         ->find($value)
                                                         ->getName();
                                    break;
                                default:
                                    $final_values[] = $value;
                                    break;
                            }
                            continue;
                        }
                        $final_values[] = $value;
                    }
                    $final_filters[$type][] = [
                        'filter_type'      => $data['filter_type'],
                        'filter_operation' => $data['filter_operation'],
                        'field'            => $data['field'],
                        'values'           => $final_values,
                    ];
                }
            }
        }

        return $final_filters;
    }

    /**
     * De-serializes the Filters using developer magic
     *
     * @param $filters
     *
     * @return array
     */
    public static function prepareFilters($filters)
    {
        $final_filters = [];
        foreach ($filters as $name => $values) {
            // Clean up the fields
            self::cleanFilterValues($name, $values, $final_filters);
        }

        return $final_filters;
    }

    /**
     * Clean up contact serialization
     *
     * @param $name
     * @param $values
     * @param $finalFilters
     *
     * @internal param $type
     */
    private static function cleanFilterValues($name, &$values, &$finalFilters)
    {
        // Divide Filter name FIELD_TABLE:FIELD_NAME:FIELD_TYPE:FILTER_TYPE:FILTER_OPERATION
        list($fieldTable, $field, $fieldType, $filterType, $filterOperation) = explode(':', $name);

        // Clean by field type
        switch ($fieldType) {
            case FormFieldTypeEnum::FIELD_TYPE_INTEGER:

                // Convert values to array
                $values = explode(',', $values);
                if (!is_array($values)) {
                    $values = [$values];
                }

                foreach ($values as $key => $value) {
                    $op = ConditionOperatorEnum::CONDITION_OPERATOR_NAME_EQUAL;
                    // Check if its a range
                    if (preg_match('/-/i', $value)) {
                        $ranges = explode('-', $value);
                        $value  = [[$ranges[0], $ranges[1]]];
                        $op     = ConditionOperatorEnum::CONDITION_OPERATOR_NAME_BETWEEN;
                    }

                    if (!empty($value)) {
                        $finalFilters[$fieldTable][$filterType][] = [
                            'filter_type'      => $filterType,
                            'filter_operation' => $filterOperation,
                            'field'            => $field,
                            'field_type'       => $fieldType,
                            'opt'              => $op,
                            'value'            => is_array($value) ? $value : [$value],

                        ];
                    }
                }
                break;
            case FormFieldTypeEnum::FIELD_TYPE_ARRAY:
            case FormFieldTypeEnum::FIELD_TYPE_BOOLEAN:

                if (empty($values)) {
                    break;
                }

                // Convert values to array
                if (!is_array($values)) {
                    $values = [$values];
                }

                // Remove empty filters
                foreach ($values as $key => $value) {
                    if (is_array($value)) {
                        $value = array_filter($value);
                    }

                    if (empty($value) && !is_bool($value)) {
                        unset($values[$key]);
                    }
                }

                if (!empty($values)) {
                    $finalFilters[$fieldTable][$filterType][] = [
                        'filter_type'      => $filterType,
                        'filter_operation' => $filterOperation,
                        'field'            => $field,
                        'field_type'       => $fieldType,
                        'opt'              => ConditionOperatorEnum::CONDITION_OPERATOR_NAME_EQUAL,
                        'value'            => $values,

                    ];
                }
                break;
            case FormFieldTypeEnum::FIELD_TYPE_CHOICE:

                if (empty($values)) {
                    break;
                }

                switch ($values) {
                    case FormFieldTypeEnum::FIELD_TYPE_CHOICE_YES:
                        $values = 1;
                        break;
                    case FormFieldTypeEnum::FIELD_TYPE_CHOICE_NO:
                        $values = 0;
                        break;
                    case FormFieldTypeEnum::FIELD_TYPE_CHOICE_BOTH:
                    default:
                        $values = null;
                        break;
                }

                if ($values !== null) {
                    $finalFilters[$fieldTable][$filterType][] = [
                        'filter_type'      => $filterType,
                        'filter_operation' => $filterOperation,
                        'field'            => $field,
                        'field_type'       => $fieldType,
                        'opt'              => ConditionOperatorEnum::CONDITION_OPERATOR_NAME_EQUAL,
                        'value'            => $values,

                    ];
                }
                break;
            case FormFieldTypeEnum::FIELD_TYPE_RANGE:

                $value = [];
                foreach ($values as $key => $v) {
                    reset($v);
                    $firstKey = key($v);

                    if (!empty($v[$firstKey])) {

                        list($start, $end) = explode('-', $v[$firstKey]);
                        $value[] = [trim($start), trim($end)];
                    }
                }

                if (!empty($value)) {
                    $finalFilters[$fieldTable][$filterType][] = [
                        'filter_type'      => $filterType,
                        'filter_operation' => $filterOperation,
                        'field'            => $field,
                        'field_type'       => $fieldType,
                        'opt'              => ConditionOperatorEnum::CONDITION_OPERATOR_NAME_BETWEEN,
                        'value'            => $value,

                    ];
                }

                break;
            case FormFieldTypeEnum::FIELD_TYPE_DATE_EQUAL:
            case FormFieldTypeEnum::FIELD_TYPE_DATE_GREATER_THAN:
            case FormFieldTypeEnum::FIELD_TYPE_DATE_LESS_THAN:
                if (empty($values)) {
                    break;
                }

                $finalFilters[$fieldTable][$filterType][] = [
                    'filter_type'      => $filterType,
                    'filter_operation' => $filterOperation,
                    'field'            => $field,
                    'field_type'       => FormFieldTypeEnum::FIELD_TYPE_DATE,
                    'opt'              => $fieldType,
                    'value'            => is_array($values) ? $values : [$values],

                ];
                break;
        }

        // If the value isn't an array convert it
        if (!is_array($values)) {
            $values = [$values];
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->filters as $group_name => $group) {
            $virtual_form = $builder->create(
                $group_name,
                'form',
                [
                    'label'      => $group['label'],
                    'virtual'    => true,
                    'label_attr' => ['class' => 'text-blue'],
                    'attr'       => $group['attr'],
                ]
            );
            foreach ($group['fields'] as $filter) {
                // Name Generator - FIELD_TABLE:FIELD_NAME:FIELD_TYPE:FILTER_TYPE:FILTER_OPERATION
                $name           = sprintf(
                    '%s:%s:%s:%s:',
                    $filter['table']
                    /*FIELD_TABLE*/,
                    $filter['field']
                    /*FIELD_NAME*/,
                    $filter['field_type']
                    /*FIELD_TYPE*/,
                    $filter['filter_type']/*FILTER_TYPE*/
                );
                $inclusion_name = $name . self::INCLUSION_FILTER/*FILTER_OPERATION*/
                ;
                $exclusion_name = $name . self::EXCLUSION_FILTER/*FILTER_OPERATION*/
                ;

                // Disable default Sonata selec2
                $filter['options']['attr']['data-sonata-select2'] = 'false';

                // Filters should always be optional
                $filter['options']['required'] = false;

                // Add INCLUSION Filter
                $virtual_form->add($inclusion_name, $filter['type'], $filter['options']);

                // Add EXCLUSION Filter
                if ($filter['has_exclusion_filter']) {

                    if (array_key_exists('type', $filter['options']) && is_object($filter['options']['type'])) {
                        // In PHP Objects are passed as reference, so a clone is needed to change parameters
                        $filter['options']['type'] = clone $filter['options']['type'];
                        $filter['options']['type']->setLabel('Exclude ' . $filter['options']['type']->getLabel());
                    } else {
                        $filter['options']['label'] = 'Exclude ' . $filter['options']['label'];
                    }

                    $virtual_form->add($exclusion_name, $filter['type'], $filter['options']);
                }
            }
            $builder->add($virtual_form);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'filters';
    }
}
