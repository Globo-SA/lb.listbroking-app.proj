<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\EventListener;

use \Gedmo\Blameable\BlameableListener as BaseBlameableListener;
use Gedmo\Blameable\Mapping\Event\BlameableAdapter;

class BlameableListener extends BaseBlameableListener {

    /**
     * Updates a field
     *
     * @param object           $object
     * @param BlameableAdapter $ea
     * @param $meta
     * @param $field
     */
    protected function updateField($object, $ea, $meta, $field)
    {
        $newValue = $this->getUserValue($meta, $field);
        if(!$newValue){
            return;
        }

        parent::updateField($object, $ea, $meta, $field);
    }
} 