<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service;


use ListBroking\AppBundle\Exception\InvalidEntityTypeException;

interface AppServiceInterface {

    /**
     * Gets a List of Entities by type
     * @param $type
     * @param $hydrate
     * @internal param $cache_id
     * @return mixed
     */
    public function getEntities($type, $hydrate = true);

    /**
     * Gets and entity by type and id
     * @param $type
     * @param $id
     * @param bool $hydrate
     * @param bool $attach
     * @return mixed|object
     * @throws InvalidEntityTypeException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntity($type, $id, $hydrate = true, $attach = false);

    /**
     * Adds a given entity
     * @param $entity
     * @return mixed
     */
    public function addEntity($entity);

    /**
     * Updates a given Entity
     * @param $entity
     * @return mixed
     */
    public function updateEntity($entity);

    /**
     * Removes a given Entity
     * @param $entity
     * @return mixed
     */
    public function removeEntity($entity);

    /**
     * @param $code
     * @param bool $hydrate
     * @return mixed
     */
    public function getCountryByCode($code, $hydrate = true);
}