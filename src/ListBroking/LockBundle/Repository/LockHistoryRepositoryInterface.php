<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Repository;


use ListBroking\DoctrineBundle\Exception\EntityClassMissingException;

interface LockHistoryRepositoryInterface {

    /**
     * Creates a new LockHistory using a Lock
     *
     * @param null|object $preset
     *
     * @throws EntityClassMissingException
     * @return mixed
     */
    public function createFromLock($preset = null);
}