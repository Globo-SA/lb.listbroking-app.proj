<?php

namespace ListBroking\AppBundle\Enum;

/**
 * ConditionOperatorEnum
 */
class ConditionOperatorEnum
{
    const CONDITION_OPERATOR_NAME_BETWEEN      = 'between';
    const CONDITION_OPERATOR_NAME_EQUAL        = 'equal';
    const CONDITION_OPERATOR_NAME_GREATER_THAN = 'greater_than';
    const CONDITION_OPERATOR_NAME_LESS_THAN    = 'less_than';

    const CONDITION_OPERATOR_EQUAL        = '=';
    const CONDITION_OPERATOR_GREATER_THAN = '>=';
    const CONDITION_OPERATOR_LESS_THAN    = '<=';

    private static $map = [
        self::CONDITION_OPERATOR_NAME_EQUAL        => self::CONDITION_OPERATOR_EQUAL,
        self::CONDITION_OPERATOR_NAME_GREATER_THAN => self::CONDITION_OPERATOR_GREATER_THAN,
        self::CONDITION_OPERATOR_NAME_LESS_THAN    => self::CONDITION_OPERATOR_LESS_THAN,
    ];

    /**
     * Convert operator name to math sintax
     *
     * @param $name
     *
     * @return string
     */
    public static function convertNameToOperator($name): string
    {
        return self::$map[$name];
    }
}
