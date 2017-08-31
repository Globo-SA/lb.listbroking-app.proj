<?php

namespace ListBroking\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Entity\Configuration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ListBroking\AppBundle\DataFixtures\ORM\LoadConfigurationData
 */
class LoadConfigurationData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 30;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $data = $this->getData();

        foreach ($data as $configData) {
            $configuration = new Configuration();
            $configuration->setName($configData['name']);
            $configuration->setType($configData['type']);
            $configuration->setValue($configData['value']);
            $this->getEntityManager()->persist($configuration);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * Get entity manager service
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Get Configuration data
     *
     * @return array
     */
    protected function getData()
    {
        return [
            [
                'name'  => 'lock.time',
                'type'  => 'string',
                'value' => '+3 months',
            ],
            [
                'name'  => 'system.email',
                'type'  => 'string',
                'value' => 'systems@adclick.pt',
            ],
            [
                'name'  => 'opposition_list.types',
                'type'  => 'json',
                'value' => '{"amd_portugal":"AMD Portugal","robinson_es":"Lista Robinson Espanha"}',
            ],
            [
                'name'  => 'opposition_list.config',
                'type'  => 'json',
                'value' => '{"amd_portugal":{"has_header":true,"phone_columns":["Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC"]},"robinson_es":{"has_header":false,"phone_columns":["AZ", "BA", "BE", "BF", "BJ", "BK", "BO","BP","BQ"]}}',
            ],
            [
                'name'  => 'extraction.contact.show_limit',
                'type'  => 'int',
                'value' => '10',
            ],
            [
                'name'  => 'batch_sizes',
                'type'  => 'json',
                'value' => '{"filter_engine": 5000, "deduplication": 5000, "deliver": 5000, "staging_import": 2000}',
            ],
            [
                'name'  => 'default_datacard',
                'type'  => 'json',
                'value' => '{"entity_country":{},"aggregation_country":true,"aggregation_gender":false,"aggregation_is_mobile":false,"aggregation_sub_category":false}',
            ],
            [
                'name'  => 'default_country_id',
                'type'  => 'int',
                'value' => '1',
            ],
            [
                'name'  => 'extraction.max_quantity',
                'type'  => 'int',
                'value' => '1000000',
            ],
            [
                'name'  => 'lock.initial_time',
                'type'  => 'string',
                'value' => '-3 months',
            ],
            [
                'name'  => 'cleanup_expire',
                'type'  => 'json',
                'value' => '{ "exception": 6, "lb_lock": 12, "extraction": 12, "staging_contact_processed": 6, "staging_contact_dqp": 6, "task": 6}',
            ],
        ];
    }
}
