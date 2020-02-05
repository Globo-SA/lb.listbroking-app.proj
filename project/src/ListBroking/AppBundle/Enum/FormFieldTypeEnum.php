<?php

namespace ListBroking\AppBundle\Enum;

/**
 * FormFieldTypeEnum
 */
class FormFieldTypeEnum
{
    const FIELD_TYPE_ARRAY              = 'array';
    const FIELD_TYPE_BOOLEAN            = 'boolean';
    const FIELD_TYPE_CHOICE             = 'choice';
    const FIELD_TYPE_CHOICE_YES         = 'yes';
    const FIELD_TYPE_CHOICE_NO          = 'no';
    const FIELD_TYPE_CHOICE_BOTH        = 'both';
    const FIELD_TYPE_DATE               = 'date';
    const FIELD_TYPE_DATE_EQUAL         = 'equal';
    const FIELD_TYPE_DATE_GREATER_THAN  = 'greater_than';
    const FIELD_TYPE_DATE_LESS_THAN     = 'less_than';
    const FIELD_TYPE_INTEGER            = 'integer';
    const FIELD_TYPE_INTEGER_AFTER_DATE = 'integer_after_date';
    const FIELD_TYPE_RANGE              = 'range';

    /**
     * Get all
     *
     * @return array
     */
    public function getAll(): array
    {
        return [
            self::FIELD_TYPE_ARRAY              => self::FIELD_TYPE_ARRAY,
            self::FIELD_TYPE_BOOLEAN            => self::FIELD_TYPE_BOOLEAN,
            self::FIELD_TYPE_CHOICE             => self::FIELD_TYPE_CHOICE,
            self::FIELD_TYPE_DATE               => self::FIELD_TYPE_DATE,
            self::FIELD_TYPE_INTEGER            => self::FIELD_TYPE_INTEGER,
            self::FIELD_TYPE_INTEGER_AFTER_DATE => self::FIELD_TYPE_INTEGER_AFTER_DATE,
            self::FIELD_TYPE_RANGE              => self::FIELD_TYPE_RANGE,
        ];
    }
}
