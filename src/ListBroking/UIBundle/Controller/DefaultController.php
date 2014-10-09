<?php

namespace ListBroking\UIBundle\Controller;

use ESO\Doctrine\ORM\QueryBuilder;
use ListBroking\ClientBundle\Entity\Campaign;
use ListBroking\ClientBundle\Entity\Client;
use ListBroking\ClientBundle\Service\ClientService;
use ListBroking\CoreBundle\Entity\Category;
use ListBroking\CoreBundle\Entity\Country;
use ListBroking\CoreBundle\Entity\SubCategory;
use ListBroking\CoreBundle\Exception\EntityValidationException;
use ListBroking\CoreBundle\Service\CoreService;
use ListBroking\ExtractionBundle\Entity\Extraction;
use ListBroking\ExtractionBundle\Entity\ExtractionTemplate;
use ListBroking\LockBundle\Engine\LockEngine;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;

class DefaultController extends Controller
{
    public function indexAction(Request $request){
        return $this->render('ListBrokingUIBundle:Default:index.html.twig', array());
    }

    public function tentugalAction(Request $request)
    {
        $core_service = $this->get('listbroking.core.service');

        $category = $core_service->getCategory(1);
        json_encode($category);

//        $form = $this->createForm('country_form', $country);
//        if ($request->getMethod() == 'POST'){
//            $form->handleRequest($request);
//
//            if ($form->isValid()){
//                $core_service->updateCountry($country);
//            }
//        }

//        try {
//            $country = new Country();
//            $country->setIsActive(0);
//            $country->setName('França');
//            $country->setIsoCode('FR');
//            $core_service->addCountry($country);
//        } catch (EntityValidationException $e) {
//            echo $e;
//        }

//        var_dump($core_service->getCountryList(false));
        return $this->render('ListBrokingUIBundle:Default:tentugal.html.twig', array());
    }


    public function samuelAction(Request $request)
    {
        $lock_service = $this->get('listbroking.lock.service');
        $e_service = $this->get('listbroking.extraction.service');

//        $lock = $lock_service->getLock(8);
//
//        $lock_repo = $this->get('listbroking.lockbundle.lock_history.repository');
//        $lock_repo->createFromLock($lock);
//
        $lock_filters = array(
            1 => array( //NoLocksFilter
                'filters' => array(
                    array(
                        'interval' =>  new \DateTime()
                    )
                )
            ),
            2 => array( //ReservedLockType
                'filters' => array(
                    array(
                        'interval' =>  new \DateTime('- 1 week')
                    )
                )
            ),
            3 => array( //ClientLockType
                'filters' =>  array(
                    array(
                        'client_id' => 2,
                        'interval' => new \DateTime('- 4 month')
                    ),
                    array(
                        'client_id' => 4,
                        'interval' => new \DateTime('- 5 week')
                    )
                )
            ),
            4 => array( //CampaignFilter
                'filters' => array(
                    array(
                        'client_id' => 4,
                        'campaign_id' => 2,
                        'interval' => new \DateTime('- 2 month')
                    )
                )
            ),
            5 => array( //Category
                'filters' =>  array(
                    array(
                        'category_id' => 2,
                        'interval' => new \DateTime('- 9 month')
                    ),
                )
            ),
            6 => array( // SubCategoryLockType
                'filters' => array(
                    array(
                        'category_id' => 2,
                        'sub_category_id' => 2,
                        'interval' => new \DateTime('- 8 month')
                    )
                )
            ),
        );

        $contact_filters = array(
            1 => array( //BaseContactFilter
                'filters' =>
                    array(
                        array(
                        'field' => 'gender',
                        'opt' => 'equal',
                        'value' => array(53)
                        ),
                        array(
                            'field' => 'country',
                            'opt' => 'equal',
                            'value' => array(75,76)
                        ),
                        array(
                            'field' => 'birthdate',
                            'opt' => 'between',
                            'value' => array('1978-01-02', '1987-06-28')
                        )
                    )
            ),
        );

//        $engine = $lock_service->startEngine();
//        $qb = $engine->compileFilters($lock_filters, $contact_filters);
//
//
//        ladybug_dump($qb->getQuery()->getSQL());
//        ladybug_dump($qb->getQuery()->getParameters()->toArray());
//        ladybug_dump_die($qb->getQuery()->getArrayResult());

//        /** @var Extraction $extraction */
//        $extraction = $e_service->getExtraction(5,true);
//        $extraction->setFilters(array('lock_filters' => $lock_filters, 'contact_filters' => $contact_filters));
//        $e_service->updateExtraction($extraction);

        $extraction = $e_service->getExtraction(5);
        $extraction_template = $e_service->getExtractionTemplate(1);
        $export_types = $e_service->getExportTypes();

        $engine = $lock_service->startEngine();
        $qb = $engine->compileFilters($extraction['filters']);
        $leads_array = $qb->getQuery()->getArrayResult();


        $e_service->exportExtraction($extraction_template,$leads_array, $export_types['Excel5']);
//        $extraction_template = new ExtractionTemplate();
//        $extraction_template->setIsActive(1);
//        $extraction_template->setName("Metlife Credit Card Extraction");
//        $extraction_template->setTemplate(array(
//            "headers" => array(
//                "firstname" => "Primeiro nome",
//                "lastname" => "Último nome",
//                "gender" => "Sexo",
//                "birthdate" => "Data de nascimento",
//                "phone" => "Telefone"
//            )
//        ));
//        $e_service->addExtractionTemplate($extraction_template);

//        $e_service->setExtractionFilters(5, $lock_filters, $contact_filters);
//        $e_service->addExtractionContactFilters(5, 1,array(
//                array(
//                    'field' => 'postalcode2',
//                    'opt' => 'equal',
//                    'value' => array(345)
//                ),
//                array(
//                    'field' => 'address',
//                    'opt' => 'equal',
//                    'value' => array('Rua Sobe e desce')
//                )
//            )
//        );
//        $e_service->addExtractionLockFilters(5, 1,array(
//                array(
//                    'category_id' => 1,
//                    'sub_category_id' => 3,
//                    'interval' => new \DateTime('- 8 month')
//                ),
//                array(
//                    'category_id' => 5,
//                    'sub_category_id' => 2,
//                    'interval' => new \DateTime('- 10 month')
//                )
//            )
//        );
//        ladybug_dump_die($e_service->getExtraction(5));

//        $extraction = $e_service->getExtraction(5, true);

//        $engine = $lock_service->startEngine();
//
//        $filters = $extraction->getFilters();
//        $qb = $engine->compileFilters($filters['lock_filters'], $filters['contact_filters']);
//

//        $e_service->updateExtraction($extraction);

        return $this->render('ListBrokingUIBundle:Default:samuel.html.twig', array());
    }
}
