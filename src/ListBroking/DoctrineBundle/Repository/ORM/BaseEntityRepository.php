<?php
/**
 *
 * @author     Bruno Escudeiro <bruno.escudeiro@adclick.pt>
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\DoctrineBundle\Repository\ORM;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use ESO\Doctrine\ORM\EntityRepository;
use ESO\Doctrine\ORM\QueryBuilder;
use ListBroking\DoctrineBundle\Exception\EntityClassMissingException;
use ListBroking\DoctrineBundle\Exception\EntityObjectInstantiationException;
use ListBroking\DoctrineBundle\Repository\BaseEntityRepositoryInterface;
use ListBroking\DoctrineBundle\Tool\InflectorToolInterface;

class BaseEntityRepository extends EntityRepository implements BaseEntityRepositoryInterface {

    /**
     * @var string
     */
    protected $entity_class;

    /**
     * @var \ListBroking\DoctrineBundle\Tool\InflectorTool
     */
    protected $inflector;

    public function __construct(
        EntityManagerInterface $entityManager,
        $entityName,
        $alias,
        $entityClass,
        InflectorToolInterface $inflectorTool
    ){
        parent::__construct($entityManager, $entityName, $alias);

        $this->entity_class = $entityClass;
        $this->inflector    = $inflectorTool;
    }

    /**
     * Find one record based on id
     *
     * @param $id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findOneById($id)
    {
        $query_builder = $this->createQueryBuilder()
            ->andWhere("{$this->alias()}.id = :id");

        $query_builder->setParameter('id', $id);

        return $query_builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Finds all entities with associations
     * eagerly fetched by default
     *
     * @param bool $eager
     * @return array
     */
    public function findAll($eager = true)
    {
        $qb = $this->createQueryBuilder();

        if($eager){
            /**
             * Gets all entity associations for passing to fetch mode and
             * Sets the fetch mode to eager for caching
             * NOTE: setFetchMode() does not work in orm 2.3
             */
            $associations_mappings =  $meta = $this->entityManager->getClassMetadata($this->entity_class)->getAssociationNames();
            foreach ($associations_mappings as $associations_mapping)
            {
                $qb->leftJoin($this->alias() . '.' . $associations_mapping, $associations_mapping);
                $qb->addSelect($associations_mapping);
            }
        }

        return $qb->getQuery()->execute(null, AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Creates a new object to be used
     *
     * @param null|object $preset
     *
     * @throws EntityClassMissingException
     * @throws EntityObjectInstantiationException
     * @return mixed
     */
    public function createNewEntity($preset = null)
    {
        if (empty($this->entity_class))
        {
            throw new EntityClassMissingException("Service declaration for {$this->getEntityName()} must have a class name to implement createNew()");
        }

        $object = new $this->entity_class();
        if (!$preset instanceof $object){
            throw new EntityObjectInstantiationException("Wrong entity instance for ". $this->getEntityName() . ". Must be " . $this->entity_class . ".");
        }

        $this->entityManager->persist($preset);

        return $preset;
    }

    /**
     * Alias for EntityManager#remove
     *
     * @param $object
     */
    public function remove($object)
    {
        $this->entityManager->remove($object);
    }

    /**
     * Alias for EntityManager#persist
     *
     * @param $object
     */
    public function persist($object)
    {
        $this->entityManager->persist($object);
    }

    /**
     * Alias for EntityMannager#flush
     */
    public function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * Updates one entity
     * @param $object
     * @return mixed|object
     */
    public function merge($object){
        return $this->entityManager->merge($object);
    }

    /**
     * Creates a new QueryBuilder instance that is pre-populated for this entity name.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return parent::createQueryBuilder();
    }


    /**
     * @return string
     */
    public function getEntityName()
    {
        return parent::getEntityName();
    }

    /**
     * @return string
     */
    public function getEntityManager()
    {
        return parent::getEntityManager();
    }

}