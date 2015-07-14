<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Form;

use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\Filter\ContactFilterInterface;
use ListBroking\AppBundle\Engine\Filter\LeadFilterInterface;
use ListBroking\AppBundle\Engine\Filter\LockFilterInterface;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Form\Type\RangeType;
use ListBroking\AppBundle\Service\Helper\AppService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FiltersType extends AbstractType
{

    // Filter Types
    const INCLUSION_FILTER = 'inclusion';

    const EXCLUSION_FILTER = 'exclusion';

    // Field Types
    const FIELD_TYPE_INTEGER = 'integer';

    const FIELD_TYPE_ARRAY   = 'array';

    const FIELD_TYPE_BOOLEAN = 'boolean';

    const FIELD_TYPE_RANGE   = 'range';

    // Operations
    const EQUAL_OPERATION   = 'equal';

    const BETWEEN_OPERATION = 'between';

    private $filters;

    /**
     * @var AppService
     */
    private $a_service;

    function __construct (AppService $appService)
    {
        $this->a_service = $appService;

        // Default Values
        $default_date_range = date('Y/m/01 - Y/m/t'); // Current month

        // Arrays for Choices
        $genders = $this->a_service->getEntities('gender', false);
        $owners = $this->a_service->getEntities('owner', false);
        $sources = $this->a_service->getEntities('source', false);
        $categories = $this->a_service->getEntities('category', false);
        $sub_categories = $this->a_service->getEntities('sub_category', false);
        $clients = $this->a_service->getEntities('client', false);
        $campaigns = $this->a_service->getEntities('campaign', false);
        $expiration_choices = array(
            ''             => '',
            '1 Week ago'   => '-1 week',
            '2 Weeks ago'  => '-2 week',
            '3 Weeks ago'  => '-3 week',
            '1 month ago'  => '-1 month',
            '2 months ago' => '-2 month',
            '3 months ago' => '-3 month',
            '4 months ago' => '-4 month',
            '5 months ago' => '-5 month',
            '6 months ago' => '-6 month',

        );

        // Filter Schema
        $this->filters = array(
            "required"                            => array(
                "label"  => "Required Fields",
                "attr"   => array("class" => "in"),
                "fields" => array(
                    array(
                        "filter_type"          => ContactFilterInterface::REQUIRED_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "gender",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "required" => false,
                            "data"     => true,
                            "attr"     => array(
                                "class" => "form-control"
                            ),
                            "label"    => "Has Gender"
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::REQUIRED_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "firstname",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "required" => false,
                            "data"     => true,
                            "attr"     => array(
                                "class" => "form-control"
                            ),
                            "label"    => "Has Firstname"
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::REQUIRED_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "lastname",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "required" => false,
                            "data"     => true,
                            "attr"     => array(
                                "class" => "form-control"
                            ),
                            "label"    => "Has Lastname"
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::REQUIRED_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "birthdate",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "class" => "form-control"
                            ),
                            "label"    => "Has Birthdate"
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::REQUIRED_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "address",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "class" => "form-control"
                            ),
                            "label"    => "Has address"
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::REQUIRED_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "postalcode1",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "class" => "form-control"
                            ),
                            "label"    => "Has Postalcode1"
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::REQUIRED_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "postalcode2",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "class" => "form-control"
                            ),
                            "label"    => "Has Postalcode2"
                        )
                    )
                )
            ),
            "contact"                             => array(
                "label"  => "Contact Details",
                "attr"   => array("class" => "in"),
                "fields" => array(
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "gender",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "choice",
                        "options"              => array(
                            "multiple"          => true,
                            "required"          => false,
                            "attr"              => array(
                                "data-select-mode" => "local",
                                "placeholder"      => "Select one or more...",
                                "class"            => "form-control"
                            ),
                            "label"             => "Genders",
                            "choices"           => $this->getChoicesArray($genders),
                            "choices_as_values" => true,
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "birthdate",
                        "field_type"           => self::FIELD_TYPE_RANGE,
                        "type"                 => "collection",
                        "options"              => array(
                            "required"     => false,
                            "attr"         => array(
                                "data-collection" => "true",
                                "class"           => "col-md-12"
                            ),
                            "type"         => new RangeType("birthdate", "birthdaterangepicker", "Birthdate Range"),
                            "allow_add"    => true,
                            "allow_delete" => true,
                            "label"        => "Birthdates",
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "date",
                        "field_type"           => self::FIELD_TYPE_RANGE,
                        "type"                 => "collection",
                        "options"              => array(
                            "required"     => false,
                            "attr"         => array(
                                "data-collection" => "true",
                                "class"           => "col-md-12"
                            ),
                            "type"         => new RangeType("date", "daterangepicker", "Date Range", $default_date_range),
                            "allow_add"    => true,
                            "allow_delete" => true,
                            "label"        => "Contact dates"
                        )
                    ),
                )
            ),
            "lead"                                => array(
                "label"  => "Lead Details",
                "attr"   => array("class" => "in"),
                "fields" => array(
                    array(
                        "filter_type"          => LeadFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "lead",
                        "field"                => "is_mobile",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "class" => "form-control"
                            ),
                            "label"    => "Only mobile numbers"
                        )
                    ),
                )
            ),
            "location"                            => array(
                "label"  => "Location",
                "attr"   => array("class" => "in"),
                "fields" => array(
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "contact",
                        "field"                => "country",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "hidden",
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "data-select-mode"          => "ajax",
                                "data-select-minimum-input" => 2,
                                "data-select-multiple"      => true,
                                "data-select-type"          => "Country",
                                "placeholder"               => "Select one or more...",
                                "class"                     => "form-control"
                            ),
                            "label"    => "Countries",
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "district",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "hidden",
                        "has_exclusion"        => true,
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "data-select-mode"          => "ajax",
                                "data-select-minimum-input" => 2,
                                "data-select-multiple"      => true,
                                "data-select-type"          => "District",
                                "placeholder"               => "Select one or more...",
                                "class"                     => "form-control"
                            ),
                            "label"    => "Districts",
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "county",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "hidden",
                        "has_exclusion"        => true,
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "data-select-mode"          => "ajax",
                                "data-select-minimum-input" => 2,
                                "data-select-multiple"      => true,
                                "data-select-type"          => "County",
                                "placeholder"               => "Select one or more...",
                                "class"                     => "form-control"
                            ),
                            "label"    => "Counties",
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "parish",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "hidden",
                        "has_exclusion"        => true,
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "data-select-mode"          => "ajax",
                                "data-select-minimum-input" => 2,
                                "data-select-multiple"      => true,
                                "data-select-type"          => "Parish",
                                "placeholder"               => "Select one or more...",
                                "class"                     => "form-control"
                            ),
                            "label"    => "Parish",
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "postalcode1",
                        "field_type"           => self::FIELD_TYPE_INTEGER,
                        "type"                 => "hidden",
                        "has_exclusion"        => true,
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "data-select-mode" => "open",
                                "placeholder"      => "Write and press enter...",
                                "class"            => "form-control"
                            ),
                            "label"    => "Postalcode1 (ex: 4000-5000, 4000)",
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "postalcode2",
                        "field_type"           => self::FIELD_TYPE_INTEGER,
                        "type"                 => "hidden",
                        "has_exclusion"        => true,
                        "options"              => array(
                            "required" => false,
                            "attr"     => array(
                                "data-select-mode" => "open",
                                "placeholder"      => "Write and press enter...",
                                "class"            => "form-control"
                            ),
                            "label"    => "Postalcode2",
                        )
                    ),
                ),
            ),
            "ownership_source_and_categorization" => array(
                "label"  => "Ownership, Source and Categorization",
                "attr"   => array("class" => "in"),
                "fields" => array(
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "owner",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "choice",
                        "options"              => array(
                            "required"          => false,
                            "multiple"          => "multiple",
                            "attr"              => array(
                                "data-select-mode" => "local",
                                "placeholder"      => "Select one or more...",
                                "class"            => "form-control"
                            ),
                            "label"             => "Owners",
                            "choices"           => $this->getChoicesArray($owners),
                            "choices_as_values" => true,
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "source",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "choice",
                        "options"              => array(
                            "required"          => false,
                            "multiple"          => "multiple",
                            "attr"              => array(
                                "data-select-mode" => "local",
                                "placeholder"      => "Select one or more...",
                                "class"            => "form-control"
                            ),
                            "label"             => "Sources",
                            "choices"           => $this->getChoicesArray($sources),
                            "choices_as_values" => true,
                        )
                    ),
                    array(
                        "filter_type"          => ContactFilterInterface::BASIC_TYPE,
                        "has_exclusion_filter" => true,
                        "table"                => "contact",
                        "field"                => "sub_category",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "choice",
                        "options"              => array(
                            "required"          => false,
                            "multiple"          => "multiple",
                            "attr"              => array(
                                "data-select-mode" => "local",
                                "placeholder"      => "Select one or more...",
                                "class"            => "form-control"
                            ),
                            "label"             => "SubCategories",
                            "choices"           => $this->getChoicesArray($sub_categories),
                            "choices_as_values" => true,
                        )
                    ),
                ),
            ),
            "basic_locks"                         => array(
                "label"  => "Basic Lead Locks",
                "attr"   => array("class" => "in"),
                "fields" => array(
                    array(
                        "filter_type"          => LockFilterInterface::NO_LOCKS_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "lock",
                        "field"                => "no_locks_lock_filter",
                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                        "type"                 => "checkbox",
                        "options"              => array(
                            "value"    => 1, //NoLocksFilter
                            "data"     => true,
                            "required" => false,
                            "attr"     => array(
                                "data-toggle"    => "tooltip",
                                "data-trigger"   => "hover",
                                "data-placement" => "top",
                                "title"          => "Only select leads without active locks, this should always be checked!",
                                "class"          => "form-control"
                            ),
                            "label"    => "Only unlocked Leads"
                        )
                    ),
                    //                    // This filter may never be needed
                    //                    array(
                    //                        "filter_type"          => LockFilterInterface::RESERVED_LOCK_TYPE,
                    //                        "has_exclusion_filter" => false,
                    //                        "table"                => "lock",
                    //                        "field"                => "reserved_lock_filter",
                    //                        "field_type"           => self::FIELD_TYPE_BOOLEAN,
                    //                        "type"                 => "checkbox",
                    //                        "options"              => array(
                    //                            "value"    => 2, //ReservedLockType
                    //                            "required" => false,
                    //                            "attr"     => array(
                    //                                "data-toggle"    => "tooltip",
                    //                                "data-trigger"   => "hover",
                    //                                "data-placement" => "top",
                    //                                "title"          => "Only Select leads that aren\"t reserved on an other extraction",
                    //                                "class"          => "form-control"
                    //                            ),
                    //                            "label"    => "Only free Leads"
                    //                        )
                    //                    ),
                )
            ),
            "client_locks"                        => array(
                "label"  => "Client Locks",
                "attr"   => array(),
                "fields" => array(
                    array(
                        "filter_type"          => LockFilterInterface::CLIENT_LOCK_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "lock",
                        "field"                => "client_lock_filter",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "collection",
                        "options"              => array(
                            "required"     => false,
                            "attr"         => array(
                                "data-collection" => "true",
                                "class"           => "col-md-12"
                            ),
                            "type"         => new LockType("client", "Client", $this->getChoicesArray($clients), $expiration_choices),
                            "allow_add"    => true,
                            "allow_delete" => true,
                            "label"        => "Client"
                        )
                    ),
                ),
            ),
            "campaign_locks"                      => array(
                "label"  => "Campaign Locks",
                "attr"   => array(),
                "fields" => array(
                    array(

                        "filter_type"          => LockFilterInterface::CAMPAIGN_LOCK_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "lock",
                        "field"                => "campaign_lock_filter",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "collection",
                        "options"              => array(
                            "required"     => false,
                            "attr"         => array(
                                "data-collection" => "true",
                                "class"           => "col-md-12"
                            ),
                            "type"         => new LockType("campaign", "Campaign", $this->getChoicesArray($campaigns), $expiration_choices),
                            "allow_add"    => true,
                            "allow_delete" => true,
                            "label"        => "Client"
                        )
                    ),
                ),
            ),
            "category_locks"                      => array(
                "label"  => "Category Locks",
                "attr"   => array(),
                "fields" => array(
                    array(
                        "filter_type"          => LockFilterInterface::CATEGORY_LOCK_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "lock",
                        "field"                => "category_lock_filter",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "collection",
                        "required"             => false,
                        "options"              => array(
                            "attr"         => array(
                                "data-collection" => "true",
                                "class"           => "col-md-12"
                            ),
                            "type"         => new LockType("category", "Category", $this->getChoicesArray($categories), $expiration_choices),
                            "allow_add"    => true,
                            "allow_delete" => true,
                            "label"        => "Category"
                        )
                    ),
                ),
            ),
            "sub_category_locks"                  => array(
                "label"  => "SubCategory Locks",
                "attr"   => array(),
                "fields" => array(
                    array(
                        "filter_type"          => LockFilterInterface::SUB_CATEGORY_LOCK_TYPE,
                        "has_exclusion_filter" => false,
                        "table"                => "lock",
                        "field"                => "sub_category_lock_filter",
                        "field_type"           => self::FIELD_TYPE_ARRAY,
                        "type"                 => "collection",
                        "required"             => false,
                        "options"              => array(
                            "attr"         => array(
                                "data-collection" => "true",
                                "class"           => "col-md-12"
                            ),
                            "type"         => new LockType("sub_category", "SubCategory", $this->getChoicesArray($sub_categories), $expiration_choices),
                            "allow_add"    => true,
                            "allow_delete" => true,
                            "label"        => "SubCategory"
                        )
                    ),
                ),
            )
        );
    }

    /**
     * Generates a choice array using a list of values
     *
     * @param $list
     *
     * @return array
     */
    private function getChoicesArray ($list)
    {
        $choices = array();
        foreach ( $list as $choice )
        {
            if ( array_key_exists('id', $choice) && array_key_exists('name', $choice) )
            {
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
    public static function validateFilter ($filter)
    {

        // Validate filter array
        if ( ! array_key_exists('filter_type', $filter) ||
             ! array_key_exists('filter_operation', $filter) ||
             ! array_key_exists('field', $filter) ||
             ! array_key_exists('field_type', $filter) ||
             ! array_key_exists('opt', $filter) ||
             ! array_key_exists('value', $filter) ||
             ! is_array($filter['value'])
        )
        {
            throw new InvalidFilterObjectException('Invalid filter, must be: array(\'filter_type\' => \'\',\'filter_operation\' => \'\',\'field\' => \'\',\'field_type\' => \'\', \'opt\' => \'\',
                    \'value\' => array()),
                    in ' . __CLASS__);
        }
    }

    /**
     * De-serializes the Filters using developer magic
     *
     * @param $filters
     *
     * @return array
     */
    public static function prepareFilters ($filters)
    {
        $final_filters = array();
        foreach ( $filters as $name => $values )
        {
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
     * @param $final_filters
     *
     * @internal param $type
     */
    private static function cleanFilterValues ($name, &$values, &$final_filters)
    {

        // Divide Filter name FIELD_TABLE:FIELD_NAME:FIELD_TYPE:FILTER_TYPE:FILTER_OPERATION
        list($field_table, $field, $field_type, $filter_type, $filter_operation) = explode(':', $name);

        // Clean by field type
        switch ( $field_type )
        {
            case FiltersType::FIELD_TYPE_INTEGER:

                // Convert values to array
                $values = explode(',', $values);
                if ( ! is_array($values) )
                {
                    $values = array($values);
                }

                foreach ( $values as $key => $value )
                {
                    $op = self::EQUAL_OPERATION;
                    // Check if its a range
                    if ( preg_match('/-/i', $value) )
                    {
                        $ranges = explode('-', $value);
                        $value = array($ranges[0], $ranges[1]);
                        $op = self::BETWEEN_OPERATION;
                    }

                    if ( ! empty($value) )
                    {
                        $final_filters[$field_table][$filter_type][] = array(
                            'filter_type'      => $filter_type,
                            'filter_operation' => $filter_operation,
                            'field'            => $field,
                            'field_type'       => $field_type,
                            'opt'              => $op,
                            'value'            => is_array($value) ? $value : array($value)

                        );
                    }
                }
                break;
            case FiltersType::FIELD_TYPE_ARRAY:
            case FiltersType::FIELD_TYPE_BOOLEAN:

                // Convert values to array
                if ( ! is_array($values) )
                {
                    $values = array($values);
                }

                // Remove empty filters
                foreach ( $values as $key => $value )
                {
                    if ( is_array($value) )
                    {
                        $value = array_filter($value);
                    }

                    if ( empty($value) )
                    {
                        unset($values[$key]);
                    }
                }

                if ( ! empty($values) )
                {
                    $final_filters[$field_table][$filter_type][] = array(
                        'filter_type'      => $filter_type,
                        'filter_operation' => $filter_operation,
                        'field'            => $field,
                        'field_type'       => $field_type,
                        'opt'              => self::EQUAL_OPERATION,
                        'value'            => $values

                    );
                }
                break;
            case FiltersType::FIELD_TYPE_RANGE:

                $value = array();
                foreach ( $values as $key => $v )
                {
                    reset($v);
                    $first_key = key($v);

                    if ( ! empty($v[$first_key]) )
                    {

                        list($start, $end) = explode('-', $v[$first_key]);
                        $value = array(trim($start), trim($end));
                    }
                }

                if ( ! empty($value) )
                {
                    $final_filters[$field_table][$filter_type][] = array(
                        'filter_type'      => $filter_type,
                        'filter_operation' => $filter_operation,
                        'field'            => $field,
                        'field_type'       => $field_type,
                        'opt'              => self::BETWEEN_OPERATION,
                        'value'            => $value

                    );
                }

                break;
        }

        // If the value isn't an array convert it
        if ( ! is_array($values) )
        {
            $values = array($values);
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
    public static function humanizeFilters (EntityManager $em, $filters_string)
    {
        $filters_array = self::prepareFilters($filters_string);

        $final_filters = array();
        foreach ( $filters_array as $type => $filters )
        {
            foreach ( $filters as $filter )
            {
                foreach ( $filter as $data )
                {
                    $final_values = array();
                    foreach ( $data['value'] as $value )
                    {
                        if(is_bool($value)){
                            $final_values[] = $value ? 'TRUE' : 'FALSE';
                            continue;
                        }

                        if(is_array($value)){
                            $final_values[] = print_r($value, 1);
                        }

                        if ($data['filter_type'] == ContactFilterInterface::BASIC_TYPE)
                        {
                            switch ( $data['field'] )
                            {
                                case 'source':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Source')
                                                         ->find($value)
                                                         ->getName()
                                    ;
                                    break;
                                case 'owner':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Owner')
                                                         ->find($value)
                                                         ->getName()
                                    ;
                                    break;
                                case 'sub_category':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:SubCategory')
                                                         ->find($value)
                                                         ->getName()
                                    ;
                                    break;
                                case 'gender':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Gender')
                                                         ->find($value)
                                                         ->getName()
                                    ;
                                    break;
                                case 'district':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:District')
                                                         ->find($value)
                                                         ->getName()
                                    ;
                                    break;
                                case 'county':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:County')
                                                         ->find($value)
                                                         ->getName()
                                    ;
                                    break;
                                case 'parish':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Parish')
                                                         ->find($value)
                                                         ->getName()
                                    ;
                                    break;
                                case 'country':
                                    $final_values[] = $em->getRepository('ListBrokingAppBundle:Country')
                                                         ->find($value)
                                                         ->getName()
                                    ;
                                    break;
                                default:
                                    $final_values[] = $value;
                                    break;
                            }
                            continue;
                        }
                        $final_values[] = $value;

                    }
                    $final_filters[$type][] = array('filter_type' => $data['filter_type'], 'filter_operation' => $data['filter_operation'], 'field' => $data['field'], 'values' => $final_values);
                }
            }

        }
        return $final_filters;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        foreach ( $this->filters as $group_name => $group )
        {
            $virtual_form = $builder->create($group_name, 'form', array(
                'label'      => $group['label'],
                'virtual'    => true,
                'label_attr' => array('class' => 'text-blue'),
                'attr'       => $group['attr'],
            ))
            ;
            foreach ( $group['fields'] as $filter )
            {
                // Name Generator - FIELD_TABLE:FIELD_NAME:FIELD_TYPE:FILTER_TYPE:FILTER_OPERATION
                $name = sprintf("%s:%s:%s:%s:", $filter["table"]/*FIELD_TABLE*/, $filter["field"]/*FIELD_NAME*/, $filter["field_type"]/*FIELD_TYPE*/, $filter["filter_type"]/*FILTER_TYPE*/);
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
                if ( $filter['has_exclusion_filter'] )
                {
                    $filter['options']['label'] = 'Exclude ' . $filter['options']['label'];
                    $virtual_form->add($exclusion_name, $filter['type'], $filter['options']);
                }
            }
            $builder->add($virtual_form);
        }
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return 'filters';
    }
}
