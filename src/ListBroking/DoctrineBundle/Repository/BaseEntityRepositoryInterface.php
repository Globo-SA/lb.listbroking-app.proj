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


interface BaseEntityRepositoryInterface {

    /**
     * Find one record based on id
     *
     * @param $id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findOneById($id);

    /**
     * Finds all entities with associations
     * eagerly fetched by default
     *
     * @param bool $eager
     * @return array
     */
    public function findAll($eager = true);

    /**
     * Creates a new object to be used
     *
     * @param null|array $preset
     *
     * @throws \ListBroking\DoctrineBundle\Exception\EntityClassMissingException
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
     * Alias for EntityMannager#flush
     */
    public function flush();

    /**
     * Creates a new QueryBuilder instance that is pre-populated for this entity name.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder();

    /**
     * Updates one entity
     * @param $object
     * @return mixed
     */
    public function merge($object);

} 