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

use Doctrine\ORM\EntityManagerInterface;
use ESO\Doctrine\ORM\EntityRepository;
use ListBroking\DoctrineBundle\Exception\EntityClassMissingException;
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

        return $query_builder->getQuery()->getOneOrNullResult();
    }

    /**
     * Creates a new object to be used
     *
     * @param null|array $preset
     *
     * @throws \ListBroking\DoctrineBundle\Exception\EntityClassMissingException
     * @return mixed
     */
    public function createNewEntity($preset = null)
    {
        if (empty($this->entity_class))
        {
            throw new EntityClassMissingException("Service declaration for {$this->getEntityName()} must have a class name to implement createNew()");
        }

        $object = new $this->entity_class();
        if (is_array($preset) && !empty($preset))
        {
            foreach ($preset as $key => $value)
            {
                $method = "set{$this->inflector->camelize($key)}";
                if (method_exists($object, $method))
                {
                    $object->$method($value);
                }
            }
        }

        $this->entityManager->persist($object);

        return $object;
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
     * Creates a new QueryBuilder instance that is pre-populated for this entity name.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return parent::createQueryBuilder();
    }

}