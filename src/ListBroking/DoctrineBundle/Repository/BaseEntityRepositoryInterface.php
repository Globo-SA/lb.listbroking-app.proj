<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\DoctrineBundle\Repository;


use ListBroking\DoctrineBundle\Exception\EntityClassMissingException;
use ListBroking\DoctrineBundle\Exception\EntityObjectInstantiationException;

interface BaseEntityRepositoryInterface {

    /**
     * Find one record based on id
     *
     * @param $id
     *
     * @param bool $hydrate
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findOneById($id, $hydrate = false);

    /**
     * Finds all entities with associations
     * eagerly fetched by default
     *
     * @param bool $eager
     * @param bool $hydrate
     * @return array
     */
    public function findAll($eager = true, $hydrate = false);

    /**
     * Creates a new object to be used
     *
     * @param null|object $preset
     *
     * @throws EntityClassMissingException
     * @throws EntityObjectInstantiationException
     * @return mixed
     */
    public function createNewEntity($preset = null);

    /**
     * Alias for EntityManager#remove
     *
     * @param $object
     */
    public function remove($object);

    /**
     * Alias for EntityManager#persist
     *
     * @param $object
     */
    public function persist($object);

    /**
     * Alias for EntityManager#flush
     */
    public function flush();

    /**
     * Alias for EntityManager#clear
     */
    public function clear();

    /**
     * Updates one entity
     * @param $object
     * @return mixed|object
     */
    public function merge($object);

    /**
     * Creates a new QueryBuilder instance that is pre-populated for this entity name.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder();


    /**
     * @return string
     */
    public function getEntityName();
    /**
     * @return string
     */
    public function getEntityManager();

    public function getEntityColumns();

    public function getColumnNames();

    public function getAssociationNames();

    /**
     * @return string
     */
    public function getEntityClass();

    /**
     * @return \ListBroking\DoctrineBundle\Tool\InflectorTool
     */
    public function getInflector();

    /**
     * @return string
     */
    public function getAlias();
}