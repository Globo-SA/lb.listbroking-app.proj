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


interface ListBrokingAdvancedConfigurationInterface {

    const GLOBAL_SCOPE = 'global';

    /**
     * Reset configuration to original values
     *  NOTE: Hard reset will restore all to original
     *
     * @param bool $hard_reset
     *
     * @return bool
     */
    function resetDefaults($hard_reset = false);

    /**
     * Get a variable value
     *
     * @param        $variable
     * @param null   $default
     *
     * @return mixed
     */
    function get($variable, $default = null);

    /**
     * Sets a variable
     *
     * @param        $variable
     * @param        $value
     *
     * @return mixed
     */
    function set($variable, $value);

    /**
     * Get a global variable value
     *
     * @param        $variable
     * @param null   $default
     *
     * @return mixed
     */
    function getOrFallbackGlobal($variable, $default = null);
} 