<?php
/**
 * Created by PhpStorm.
 * User: nunosaraiva
 * Date: 9/3/15
 * Time: 11:08 AM
 */

namespace ListBroking\AppBundle\Parser;

class DateTimeParser
{

    /**
     * Converts date to a valid datetime object
     * @param $date
     *
     * @return \DateTime
     */
    public static function getValidDateObject ($date)
    {
        if ( ! $date || is_string($date) )
        {
            return DateTimeParser::stringToDateTime($date);
        }

        return $date;
    }

    /**
     * Convert string to datetime object
     * @param $string
     *
     * @return \DateTime
     */
    private static function stringToDateTime ($string)
    {
        $datetime_object = \DateTime::createFromFormat('Y-m-d', $string);

        if ( $datetime_object )
        {
            return $datetime_object;
        }

        $datetime_object = \DateTime::createFromFormat('Y-m-d H:i:s', $string);

        if ( $datetime_object )
        {
            return $datetime_object;
        }

        return new \DateTime();
    }
} 