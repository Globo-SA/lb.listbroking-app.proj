<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AdvancedConfigurationBundle\Service;


use Adclick\AdvancedConfigurationBundle\Service\AdvancedConfigurationInterface;

class ListBrokingAdvancedConfiguration implements ListBrokingAdvancedConfigurationInterface {

    protected $service;

    function __construct(AdvancedConfigurationInterface $advancedConfigurationInterface)
    {
        $this->service = $advancedConfigurationInterface;
    }

    /**
     * Reset configuration to original values
     *  NOTE: Hard reset will restore all to original
     *
     * @param bool $hard_reset
     *
     * @return bool
     */
    function resetDefaults($hard_reset = false)
    {
        return $this->service->resetDefaults($hard_reset);
    }

    /**
     * Get a variable value
     *
     * @param        $variable
     * @param null   $default
     *
     * @return mixed
     */
    function get($variable, $default = null)
    {
        return $this->service->get($variable, $default, ListBrokingAdvancedConfigurationInterface::GLOBAL_SCOPE);
    }

    /**
     * Sets a variable
     *
     * @param        $variable
     * @param        $value
     *
     * @return mixed
     */
    function set($variable, $value)
    {
        return $this->service->set($variable, $value, ListBrokingAdvancedConfigurationInterface::GLOBAL_SCOPE);
    }

    /**
     * Get a local or fallback to global variable value
     *
     * @param        $variable
     * @param null   $default
     *
     * @return mixed
     */
    function getOrFallbackGlobal($variable, $default = null)
    {
        if (($value = $this->get($variable)) === null)
        {
            $value = $this->getGlobal($variable);
        }

        return $value;
    }


} 