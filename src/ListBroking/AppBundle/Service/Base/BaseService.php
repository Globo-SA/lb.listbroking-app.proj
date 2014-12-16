<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Base;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\UnitOfWork;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Category;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\Configuration;
use ListBroking\AppBundle\Entity\Country;
use ListBroking\AppBundle\Entity\County;
use ListBroking\AppBundle\Entity\District;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\ExtractionDeduplicationQueue;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use ListBroking\AppBundle\Entity\Gender;
use ListBroking\AppBundle\Entity\Owner;
use ListBroking\AppBundle\Entity\Parish;
use ListBroking\AppBundle\Entity\Source;
use ListBroking\AppBundle\Entity\SubCategory;
use ListBroking\AppBundle\Exception\InvalidEntityTypeException;
use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;

abstract class BaseService implements BaseServiceInterface
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var OutputInterface
     */
    protected $output;

    protected $doctrine;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Cache
     */
    protected $dcache;

    /**
     * @var FormFactory
     */
    protected $form_factory;

    /**
     * @param Logger $logger
     * @return mixed
     */
    public function setLogger(Logger $logger){
        $this->logger = $logger;
    }

    /**
     * Used to add an OutputInterface for commands
     * @param $outputInterface $interface
     * @return mixed
     */
    public function setOutputInterface(OutputInterface $outputInterface)
    {
        $this->output = $outputInterface;
    }

    /**
     * Used to get the OutputInterface for commands
     * @return mixed
     */
    public function getOutputInterface()
    {
        $this->output;
    }

    /**
     * Log system
     * @param $msg
     * @return mixed
     */
    public function log($msg)
    {
        // Outputs to the console
        if($this->output){
            $this->output->writeln($msg);
        }

        //logger
        $this->logger->info($msg);
    }

    public function setDoctrine($doctrine){
        $this->doctrine = $doctrine;
    }

    /**
     * @param EntityManager $entityManager
     * @return mixed|void
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param Cache $cache
     * @return mixed|void
     */
    public function setCache(Cache $cache)
    {
       $this->dcache = $cache;
    }

    /**
     * @param FormFactory $formFactory
     * @return mixed|void
     */
    public function setFormFactory(FormFactory $formFactory)
    {
       $this->form_factory = $formFactory;
    }

    /**
     * Attaches an entity to the EntityManager
     * if needed
     * @param $entity
     * @return void
     */
    public function attach(&$entity)
    {
        if($this->em->getUnitOfWork()->getEntityState($entity) == UnitOfWork::STATE_DETACHED){
            $entity = $this->em->merge($entity);
        }
    }

    /**
     * Gets a List of Entities by type
     * @param $type
     * @param $hydrate
     * @internal param $cache_id
     * @return mixed
     */
    public function getEntities($type, $hydrate = true)
    {
        // Entity information
        $entity_info = $this->getCacheIdAndRepo($type, $hydrate);

        // Check if there's cache
        if (!$this->dcache->contains($entity_info['cache_id']))
        {
            $repo = $this->em->getRepository($entity_info['repo_name']);
            $entities = $repo->createQueryBuilder('e')->getQuery()->getResult($entity_info['hydration_mode']);
            $this->dcache->save($entity_info['cache_id'], $entities);
        }

        return $this->dcache->fetch($entity_info['cache_id']);
    }

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
    public function getEntity($type, $id, $hydrate = true, $attach = false)
    {
        // Entity information
        $entity_info = $this->getCacheIdAndRepo($type, $hydrate, $id);

        // Check if there's cache
        if (!$this->dcache->contains($entity_info['cache_id']) || $attach)
        {
            $repo = $this->em->getRepository($entity_info['repo_name']);
            $entity = $repo->createQueryBuilder('e')
                ->where('e = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult($entity_info['hydration_mode']);
            if ($entity)
            {
                $this->dcache->save($entity_info['cache_id'], $entity);
            }

            // Return the attached entity
            if ($attach)
            {
                return $entity;
            }
        }

        // Fetch from cache
        $entity = $this->dcache->fetch($entity_info['cache_id']);

        return $entity;
    }

    /**
     * Adds a given entity
     * @param $type
     * @param $entity
     * @return mixed
     */
    public function addEntity($type, $entity)
    {
        // Entity information (not used for now)
        $entity_info = $this->getCacheIdAndRepo($type, true);

        if ($entity)
        {
            $this->em->persist($entity);
            $this->em->flush();
        }

        // Clear list cache
        $this->clearCache($entity);
    }

    /**
     * Updates a given Entity
     * @param $type
     * @param $entity
     * @return mixed
     */
    public function updateEntity($type, $entity)
    {
        if ($entity)
        {
            // Entity information
            $entity_info = $this->getCacheIdAndRepo($type, true, $entity->getId());

            $this->attach($entity);
            $this->em->flush();

            // Clear list cache & entity cache
            $this->clearCache($entity, $entity_info['cache_id']);
        }
    }

    /**
     * Removes a given Entity
     * @param $type
     * @param $entity
     * @return mixed
     */
    public function removeEntity($type, $entity)
    {
        if ($entity)
        {
            // Entity information
            $entity_info = $this->getCacheIdAndRepo($type, true, $entity->getId());

            $this->attach($entity);

            // Remove entity
            $this->em->remove($entity);
            $this->em->flush();

            // Clear list cache & entity cache
            $this->clearCache($entity, $entity_info['cache_id']);
        }
    }

    /**
     * Gets a configuration
     * @param $name
     * @return mixed
     */
    public function getConfig($name)
    {
        $entities = $this->getEntities('configuration');
        foreach ($entities as $entity){
            if($entity->getName() == $name){
                return $entity;
            }
        }

        return null;
    }

    /**
     * Generates a new form view
     * @param $type
     * @param bool $view
     * @param null $data
     * @param $action
     * @return FormBuilderInterface|Form
     */
    function generateForm($type, $action = null, $data = null, $view = false)
    {
        $form = $this->form_factory->createBuilder($type, $data);
        if($action){
            $form->setAction($action);
        }

        if ($view)
        {
            return $form->getForm()->createView();
        }
        return $form->getForm();
    }

    /**
     * Clears list cache
     * @param $entity
     * @param null $extra
     */
    private function clearCache($entity, $extra = null)
    {
        $cache_id = $entity::CACHE_ID;
        $cache_id_array = $cache_id . '_array';

        $this->dcache->delete($cache_id);
        $this->dcache->delete($cache_id_array);

        if($extra){
            $this->dcache->delete($extra);
        }
    }

    /**
     * Gets a Entity type cache and repo info
     * @param $type
     * @param $hydrate
     * @param $uniqid
     * @throws InvalidEntityTypeException
     * @return array
     */
    private function getCacheIdAndRepo($type, $hydrate, $uniqid = null)
    {

        $entity_info['hydration_mode'] = $hydrate ? Query::HYDRATE_OBJECT : Query:: HYDRATE_ARRAY;

        //TODO: Rethink this system, its not very flexible
        switch ($type)
        {
            case 'client':
                $entity_info['cache_id'] = $hydrate ? Client::CACHE_ID : Client::CACHE_ID . '_array';;
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Client';
                break;
            case 'campaign':
                $entity_info['cache_id'] = $hydrate ? Campaign::CACHE_ID : Campaign::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Campaign';
                break;
            case 'category':
                $entity_info['cache_id'] = $hydrate ? Category::CACHE_ID : Category::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Category';
                break;
            case 'sub_category':
                $entity_info['cache_id'] = $hydrate ? SubCategory::CACHE_ID : SubCategory::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:SubCategory';
                break;
            case 'country':
                $entity_info['cache_id'] = $hydrate ? Country::CACHE_ID : Country::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Country';
                break;
            case 'extraction':
                $entity_info['cache_id'] = $hydrate ? Extraction::CACHE_ID : Extraction::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Extraction';
                break;
            case 'extraction_template':
                $entity_info['cache_id'] = $hydrate ? ExtractionTemplate::CACHE_ID : ExtractionTemplate::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:ExtractionTemplate';
                break;
            case 'extraction_deduplication':
                $entity_info['cache_id'] = $hydrate ? ExtractionDeduplication::CACHE_ID : ExtractionDeduplication::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:ExtractionDeduplication';
                break;
            case 'extraction_deduplication_queue':
                $entity_info['cache_id'] = $hydrate ? ExtractionDeduplicationQueue::CACHE_ID : ExtractionDeduplicationQueue::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:ExtractionDeduplicationQueue';
                break;
            case 'gender':
                $entity_info['cache_id'] = $hydrate ? Gender::CACHE_ID : Gender::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Gender';
                break;
            case 'district':
                $entity_info['cache_id'] = $hydrate ? District::CACHE_ID : District::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:District';
                break;
            case 'county':
                $entity_info['cache_id'] = $hydrate ? County::CACHE_ID : County::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:County';
                break;
            case 'parish':
                $entity_info['cache_id'] = $hydrate ? Parish::CACHE_ID : Parish::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Parish';
                break;
            case 'owner':
                $entity_info['cache_id'] = $hydrate ? Owner::CACHE_ID : Owner::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Owner';
                break;
            case 'source':
                $entity_info['cache_id'] = $hydrate ? Source::CACHE_ID : Source::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Source';
                break;
            case 'configuration':
                $entity_info['cache_id'] = $hydrate ? Configuration::CACHE_ID : Configuration::CACHE_ID . '_array';
                $entity_info['repo_name'] = 'ListBrokingAppBundle:Configuration';
                break;
            default:
                throw new InvalidEntityTypeException("The Entity type {$type} is invalid.");
                break;
        }

        if($uniqid){

           $entity_info['cache_id'] .= "_{$uniqid}";
        }

        return $entity_info;
    }
} 