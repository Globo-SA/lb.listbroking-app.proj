<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Category;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Country;
use ListBroking\AppBundle\Entity\County;
use ListBroking\AppBundle\Entity\District;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Gender;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\Owner;
use ListBroking\AppBundle\Entity\Parish;
use ListBroking\AppBundle\Entity\Source;
use ListBroking\AppBundle\Entity\SubCategory;
use ListBroking\AppBundle\Service\Factory\OppositionListFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTestData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private $phoneIndex = [];

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $campaigns    = [];
        $clients      = [];
        $clientsNames = ['Metlife', 'Tentugals'];

        foreach ($clientsNames as $clientName) {
            $client = new Client();
            $client->setName($clientName);
            $client->setAccountName('Account '.$clientName);
            $client->setEmailAddress($clientName.'@'.$clientName.'.com');
            $client->setPhone($this->getUniquePhone());

            for ($i = 0; $i < 2; $i++) {
                $campaign = new Campaign();
                $campaign->setName($clientName.' Campaign '.$i);
                $campaign->setClient($client);
                $campaign->setDescription('coiso');

                $manager->persist($campaign);

                $campaigns[] = $campaign;
            }

            $manager->persist($client);

            $clients[] = $client;
        }

        // Some countries...
        $countries      = [];
        $countriesNames = ['PT', 'ES', 'FR'];

        foreach ($countriesNames as $isoId) {
            $country = new Country();
            $country->setName($isoId);

            $manager->persist($country);

            $countries[] = $country;
        }

        // Some owners and sources
        $ownersSources = [
            'adclick'  => ['ncursos.pt', 'e-konomista.com', 'sapo.pt', 'google.pl'],
            'that_guy' => []
        ];

        $owners  = [];
        $sources = [];

        foreach ($ownersSources as $ownerName => $sourceNames) {
            $owner = new Owner();
            $owner->setName($ownerName);
            $owner->setEmail(sprintf('%s@%s.com', $ownerName, $ownerName));
            $owner->setPhone($this->getUniquePhone());
            $owner->setCountry($countries[array_rand($countries, 1)]);

            $manager->persist($owner);

            $owners[] = $owner;

            foreach ($sourceNames as $sourceName) {
                $source = new Source();
                $source->setName($sourceName);
                $source->setCountry($countries[array_rand($countries, 1)]);
                $source->setOwner($owner);
                $source->setExternalId(sprintf('ext_%s', $sourceName));

                $manager->persist($source);

                $sources[] = $source;
            }
        }

        // Some Categories and Sub Categories
        $categories      = [];
        $subCategories   = [];
        $categoriesNames = [
            'Finance'   => ['credit', 'cards', 'consolidation'],
            'Education' => [],
            'Insurance' => ['personal', 'car', 'home'],
        ];

        foreach ($categoriesNames as $categoryName => $subCategoriesNames) {
            $category = new Category();
            $category->setName($categoryName);

            foreach ($subCategoriesNames as $subCategoryName) {
                $sub_category = new SubCategory();
                $sub_category->setName($subCategoryName);
                $sub_category->setCategory($category);

                $manager->persist($sub_category);

                $subCategories[] = $sub_category;
            }

            $manager->persist($category);

            $categories[] = $category;
        }

        // Some Genders
        $genders      = [];
        $gendersNames = ['M', 'F'];

        foreach ($gendersNames as $genderId) {
            $gender = new Gender();
            $gender->setName($genderId);

            $manager->persist($gender);

            $genders[] = $gender;
        }

        // Some Parishes
        $parishes      = [];
        $parishesNames = ['Ramalde', 'Aldoar', 'Paranhos'];

        foreach ($parishesNames as $parishName) {
            $parish = new Parish();
            $parish->setName($parishName);

            $manager->persist($parish);

            $parishes[] = $parish;
        }

        // Some Counties
        $counties      = [];
        $countiesNames = ['Porto'];

        foreach ($countiesNames as $countyName) {
            $county = new County();
            $county->setName($countyName);

            $manager->persist($county);

            $counties[] = $county;
        }

        // Some District
        $districts      = [];
        $districtsNames = ['Porto'];

        foreach ($districtsNames as $districtName) {
            $district = new District();
            $district->setName($districtName);

            $manager->persist($district);

            $districts[] = $district;
        }

        // Random Leads
        $phoneIndicatives = [9, 5, 2];

        for ($i = 0; $i < 1; $i++) {
            $indicative = $phoneIndicatives[array_rand($phoneIndicatives, 1)];

            $lead = new Lead();
            $lead->setCountry($countries[array_rand($countries, 1)]);
            $lead->setPhone($this->getUniquePhone());
            $lead->setIsMobile($indicative == 9 ? 1 : 0);
            $lead->setInOpposition(0);

            for ($j = 0; $j < 2; $j++) {
                $contact = new Contact();
                $contact->setSubCategory($subCategories[array_rand($subCategories, 1)]);
                $contact->setGender($genders[array_rand($genders, 1)]);
                $contact->setParish($parishes[array_rand($parishes, 1)]);
                $contact->setCounty($counties[array_rand($counties, 1)]);
                $contact->setDistrict($districts[array_rand($districts, 1)]);
                $contact->setCountry($countries[array_rand($countries, 1)]);
                $contact->setOwner($owners[array_rand($owners, 1)]);
                $contact->setLead($lead);
                $contact->setDate(new \DateTime(('2016'.'-0'.rand(1, 9).'-'.rand(10, 28))));
                $contact->setEmail(rand(10000000, 99999999).'@test.com');
                $contact->setBirthdate(new \DateTime(('19'.rand(50, 95).'-0'.rand(1, 9).'-'.rand(10, 28))));
                $contact->setAddress('Rua '.rand(10000000, 99999999));
                $contact->setFirstname('Dont care');
                $contact->setLastname('Dont care again');
                $contact->setIpaddress('127.0.0.1');
                $contact->setPostalcode1(rand(1000, 9999));
                $contact->setPostalcode2(rand(100, 999));
                $contact->setSource($sources[array_rand($sources, 1)]);

                $manager->persist($contact);
            }
            $manager->persist($lead);
        }

        // This data will be used on functional tests
        $lead = new Lead();
        $lead->setCountry($countries[array_rand($countries, 1)]);
        $lead->setPhone(919191919);
        $lead->setIsMobile(true);
        $lead->setInOpposition(0);
        $manager->persist($lead);

        $oppositionListFactory = new OppositionListFactory();
        $opposition            = $oppositionListFactory->create('ADCLICK', '919999999');
        $manager->persist($opposition);

        $extraction = new Extraction();
        $extraction->setName('Test');
        $extraction->setCampaign($campaign);
        $extraction->setQuantity(100);
        $extraction->setPayout(0.1);
        $extraction->setFilters([]);
        $manager->persist($extraction);

        $manager->flush();
    }

    private function getUniquePhone()
    {
        do {
            $phone = rand(900000000, 999999999);

        } while (isset($this->phoneIndex[$phone]));
        $this->phoneIndex[$phone] = true;

        return $phone;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }
}
