<?php

namespace ListBroking\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ListBroking\AppBundle\DataFixtures\ORM\LoadExtractionTemplateData
 */
class LoadExtractionTemplateData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
        return 40;
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

        foreach ($data as $extractionTemplateData) {
            $extractionTemplate = new ExtractionTemplate();
            $extractionTemplate->setName($extractionTemplateData['name']);
            $extractionTemplate->setTemplate($extractionTemplateData['template']);
            $this->getEntityManager()->persist($extractionTemplate);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * Get Entity Manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Get extraction template data
     *
     * @return array
     */
    protected function getData()
    {
        return [
            [
                'name'     => 'Deduplication Template',
                'template' => "{\r\n    \"headers\": {\r\n        \"Phone\": \"lead.phone\"\r\n    },\r\n    \"extension\": \"xls\"\r\n}",
            ],
            [
                'name'     => 'Standard Template  (With postalcode)',
                'template' => "{\r\n    \"headers\": {\r\n        \"ID\": \"contact.id\",\r\n        \"External ID\": \"contact.external_id\",\r\n        \"Firstname\": \"contact.firstname\",\r\n        \"Lastname\": \"contact.lastname\",\r\n        \"Gender\": \"gender.name\",\r\n        \"Birthdate\": \"contact.birthdate\",\r\n        \"Phone\": \"lead.phone\",\r\n        \"Postalcode1\": \"contact.postalcode1\",\r\n        \"Postalcode2\": \"contact.postalcode2\",\r\n        \"Acquisition date\": \"contact.date\"\r\n    },\r\n    \"extension\": \"xls\"\r\n}"
            ],
            [
                'name'     => 'Standard Template  (Without postalcode)',
                'template' => "{\r\n    \"headers\": {\r\n        \"ID\": \"contact.id\",\r\n        \"External ID\": \"contact.external_id\",\r\n        \"Firstname\": \"contact.firstname\",\r\n        \"Lastname\": \"contact.lastname\",\r\n        \"Gender\": \"gender.name\",\r\n        \"Birthdate\": \"contact.birthdate\",\r\n        \"Phone\": \"lead.phone\",\r\n        \"Acquisition date\": \"contact.date\"\r\n    },\r\n    \"extension\": \"xls\"\r\n}"
            ],
            [
                'name'     => 'Deduplication Template (with postalcode1, postalcode2, district, county)',
                'template' => "{\r\n    \"headers\": {\r\n        \"ID\": \"contact.id\",\r\n        \"External ID\": \"contact.external_id\",\r\n        \"Firstname\": \"contact.firstname\",\r\n        \"Lastname\": \"contact.lastname\",\r\n        \"Gender\": \"gender.name\",\r\n        \"Birthdate\": \"contact.birthdate\",\r\n        \"Phone\": \"lead.phone\",\r\n        \"Postalcode1\": \"contact.postalcode1\",\r\n        \"Postalcode2\": \"contact.postalcode2\",\r\n        \"District\": \"district.name\",\r\n        \"County\": \"county.name\",\r\n        \"Acquisition date\": \"contact.date\"\r\n    },\r\n    \"extension\": \"xls\"\r\n}"
            ],
            [
                'name'     => 'Deduplication Template (with postalcode1, postalcode2, district, county, source, acquisition date)',
                'template' => "{\r\n    \"headers\": {\r\n        \"ID\": \"contact.id\",\r\n        \"External ID\": \"contact.external_id\",\r\n        \"Firstname\": \"contact.firstname\",\r\n        \"Lastname\": \"contact.lastname\",\r\n        \"Gender\": \"gender.name\",\r\n        \"Birthdate\": \"contact.birthdate\",\r\n       \"Email\" : \"contact.email\",\r\n        \"Phone\": \"lead.phone\",\r\n        \"Postalcode1\": \"contact.postalcode1\",\r\n        \"Postalcode2\": \"contact.postalcode2\",\r\n        \"District\": \"district.name\",\r\n        \"County\": \"county.name\",\r\n        \"Source\": \"source.name\",\r\n        \"Acquisition date\": \"contact.date\"\r\n    },\r\n    \"extension\": \"xls\"\r\n}"
            ],
            [
                'name'     => 'Template BarclayCard ( ID,	External ID,	 SEXO, NOME (completo), CP4, DISTRITO, CONCELHO, TELEMOVEL, ORIGEM, SOURCE)',
                'template' => "{\r\n    \"headers\": {\r\n        \"ID\": \"contact.id\",\r\n        \"External ID\": \"contact.external_id\",\r\n        \"SEXO\": \"gender.name\",\r\n        \"NOME\": \"CONCAT_WS(' ',contact.firstname, contact.lastname) \",\r\n        \"CP4\": \"contact.postalcode1\",\r\n        \"DISTRITO\": \"district.name\",\r\n        \"CONCELHO\": \"county.name\",\r\n        \"TELEMOVEL\": \"lead.phone\",\r\n        \"ORIGEM\": \"owner.name\",\r\n        \"DATA_NASCIMENTO\": \"contact.birthdate\",\r\n        \"Acquisition Date\": \"contact.date\",\r\n         \"Source\": \"source.name\"\r\n    },\r\n    \"extension\": \"xls\"\r\n}"
            ],
            [
                'name'     => 'Contact Cleaning  Template',
                'template' => "{\r\n\t\"headers\": {\r\n\t\t\"external_id\": \"NULL\",\r\n\t\t\"contact_id\": \"contact.id\",\r\n\t\t\"phone\": \"NULL\",\r\n\t\t\"email\": \"NULL\",\r\n\t\t\"firstname\": \"contact.firstname\",\r\n\t\t\"lastname\": \"contact.lastname\",\r\n\t\t\"birthdate\": \"contact.birthdate\",\r\n\t\t\"address\": \"contact.address\",\r\n\t\t\"postalcode1\": \"contact.postalcode1\",\r\n\t\t\"postalcode2\": \"contact.postalcode2\",\r\n\t\t\"ipaddress\": \"NULL\",\r\n\t\t\"gender\": \"gender.name\",\r\n\t\t\"district\": \"district.name\",\r\n\t\t\"county\": \"county.name\",\r\n\t\t\"parish\": \"parish.name\",\r\n\t\t\"country\": \"NULL\",\r\n\t\t\"source_name\": \"NULL\",\r\n\t\t\"source_external_id\": \"NULL\",\r\n\t\t\"source_country\": \"NULL\",\r\n\t\t\"sub_category\": \"NULL\",\r\n\t\t\"date\": \"NULL\",\r\n\t\t\"initial_lock_expiration_date\": \"NULL\",\r\n\t\t\"post_request\": \"NULL\"\r\n\t},\r\n\t\"extension\": \"csv\"\r\n}"
            ],
            [
                'name'     => 'Kapta (Nombre y apellidos, código postal, población, provincia, teléfono, edad, sexo, sorteo similar y fecha de Captación.)',
                'template' => "{\r\n\t\"headers\": {\r\n\t\t\"ID\": \"contact.id\",\r\n\t\t\"External ID\": \"contact.external_id\",\r\n                \"SEXO\": \"gender.name\",\r\n  \"NOMBRE\": \"contact.firstname\",\r\n        \"APELLIDO\": \"contact.lastname\",\r\n\t\t\"CODIGO-POSTAL\": \"contact.postalcode1\",\r\n\t\t\"POBLACION\": \"IFNULL(district.name,0)\",\r\n\t\t\"PROVINCIA\": \"IFNULL(county.name,0)\",\r\n\t\t\"TELEFONO\": \"lead.phone\",\r\n\t\t\"EDAD\": \"TRUNCATE(DATEDIFF(CURDATE(),contact.birthdate)\/365.25,0)\",\r\n\t\t\"P\u00c1GINA DE SUSCRIPCI\u00d3N\": \"source.name\",\r\n\t\t\"FECHA DE CAPTACION\": \"contact.date\",\r\n \"ORIGEN\": \"owner.name\"\r\n\t},\r\n\t\"extension\": \"xls\"\r\n}"
            ],
            [
                'name'     => 'Standard Email Template (With postalcode)',
                'template' => "{\r\n\"headers\": {\r\n\"ID\": \"contact.id\",\r\n\"External ID\": \"contact.external_id\",\r\n\"Firstname\": \"contact.firstname\",\r\n\"Lastname\": \"contact.lastname\",\r\n\"Gender\": \"gender.name\",\r\n\"Birthdate\": \"contact.birthdate\",\r\n\"Email\": \"contact.email\",\r\n\"Postalcode1\": \"contact.postalcode1\",\r\n\"Postalcode2\": \"contact.postalcode2\",\r\n\"Acquisition date\": \"contact.date\"\r\n},\r\n\"extension\": \"xls\"\r\n}"
            ],
            [
                'name'     => 'Template SMS OK',
                'template' => "{\r\n\t\"headers\": {\r\n\t\t\"To\": \"lead.phone\",\r\n\t\t\"SMS OK\": \"lead.is_sms_ok\"\r\n\t},\r\n\t\"extension\": \"csv\"\r\n}"
            ],
        ];
    }
}
