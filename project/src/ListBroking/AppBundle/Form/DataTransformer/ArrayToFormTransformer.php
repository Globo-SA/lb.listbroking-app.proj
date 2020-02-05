<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class ArrayToFormTransformer implements DataTransformerInterface
{

    /**
     * Transforms a Array (text) to an object (JsonArray).
     *
     * @param mixed $array
     *
     * @internal param string $number
     * @return mixed|null
     */
    public function reverseTransform ($array)
    {
        if ( ! $array )
        {
            return null;
        }

        return json_encode($array, true);
    }

    /**
     * Transforms an object (JsonArray) to an Array (text).
     *
     * @param mixed $json
     *
     * @internal param Issue|null $issue
     * @return string
     */
    public function transform ($json)
    {
        if ( null === $json )
        {
            return array();
        }

        return json_decode($json, true);
    }
}