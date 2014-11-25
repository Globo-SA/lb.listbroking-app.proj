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

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\Category;
use ListBroking\AppBundle\Entity\Country;
use ListBroking\AppBundle\Entity\SubCategory;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\County;
use ListBroking\AppBundle\Entity\District;
use ListBroking\AppBundle\Entity\Gender;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\Owner;
use ListBroking\AppBundle\Entity\Parish;
use ListBroking\AppBundle\Entity\Source;

class LoadTestData implements FixtureInterface {

    /**
     * {@inheritDoc}
     */
    function load(ObjectManager $manager)
    {
        ini_set('memory_limit', '-1');
        $campaigns = array();
        $clients = array();
        $clients_names = array('Metlife', 'Tentugals');
        foreach($clients_names as $client_name){
            $client = new Client();
            $client->setName($client_name);
            $client->setIsActive(1);
            $client->setAccountName('Account ' . $client_name);
            $client->setEmailAddress($client_name . '@' . $client_name . '.com');
            $client->setPhone(rand(900000000,999999999));

            for($i=0; $i < 2; $i++){
                $campaign = new Campaign();
                $campaign->setName($client_name . ' Campaign ' . $i);
                $campaign->setIsActive(1);
                $campaign->setClient($client);
                $campaign->setDescription('coiso');

                $manager->persist($campaign);

                $campaigns[] = $campaign;
            }

            $manager->persist($client);

            $clients[] = $client;
        }

        // Some countries...
        $countries = array();
        $countries_names = array('PT', 'ES', 'FR');
        foreach($countries_names as $iso_id){
            $country = new Country();
            $country->setIsActive(1);
            $country->setIsoCode($iso_id);
            $country->setName($iso_id);

            $manager->persist($country);

            $countries[] = $country;
        }

        // Some owners
        $owners = array();
        $owners_names = array('adclick', 'that_guy');
        foreach($owners_names as $owner_name){
            $owner = new Owner();
            $owner->setName($owner_name);
            $owner->setIsActive(1);
            $owner->setEmail($owner_name . '@' . $owner_name . '.com');
            $owner->setPhone(rand(900000000,999999999));
            $owner->setCountry($countries[array_rand($countries,1)]);

            $manager->persist($owner);

            $owners[] = $owner;
        }

        // Some sources
        $sources = array();
        $sources_names = array('ncursos.pt', 'e-konomista.com', 'sapo.pt', 'google.pl');
        foreach($sources_names as $source_name){
            $source = new Source();
            $source->setName($source_name);
            $source->setIsActive(1);
            $source->setCountry($countries[array_rand($countries,1)]);
            $source->setOwner($owners[array_rand($owners,1)]);

            $manager->persist($source);

            $sources[] = $source;
        }

        // Some Categories and Sub Categories
        $categories = array();
        $sub_categories = array();
        $categories_names = array(
            "Finance" => array(
            "credit", "cards", "consolidation"
            ),
            "Education" => array(

            ),
            "Insurance" => array(
              "personal", "car", "home"
            ));
        foreach($categories_names as $category_name => $sub_categories_names){
            $category = new Category();
            $category->setName($category_name);
            $category->setIsActive(1);

            foreach($sub_categories_names as $sub_category_name){
                $sub_category = new SubCategory();
                $sub_category->setIsActive(1);
                $sub_category->setName($sub_category_name);
                $sub_category->setCategory($category);

                $manager->persist($sub_category);

                $sub_categories[] = $sub_category;
            }

            $manager->persist($category);

            $categories[] = $category;
        }

        // Some Genders
        $genders = array();
        $genders_names = array('M','F');
        foreach($genders_names as $gender_id){
            $gender = new Gender();
            $gender->setName($gender_id);

            $manager->persist($gender);

            $genders[] = $gender;
        }

        // Some Parishes
        $parishes = array();
        $parishes_names = array('Ramalde', 'Aldoar', 'Paranhos');
        foreach($parishes_names as $parish_name){
            $parish = new Parish();
            $parish->setName($parish_name);

            $manager->persist($parish);

            $parishes[] = $parish;
        }

        // Some Counties
        $counties = array();
        $counties_names = array('Porto');
        foreach($counties_names as $county_name){
            $county = new County();
            $county->setName($county_name);

            $manager->persist($county);

            $counties[] = $county;
        }

        // Some District
        $districts = array();
        $districts_names = array('Porto');
        foreach($districts_names as $district_name){
            $district = new District();
            $district->setName($district_name);

            $manager->persist($district);

            $districts[] = $district;
        }

        // Random Leads
        $phone_indicatives = array(9, 5, 2);
        for($i = 0; $i < 10000; $i++){

            $indicative = $phone_indicatives[array_rand($phone_indicatives, 1)];
            $lead = new Lead();
            $lead->setCountry($countries[array_rand($countries, 1)]);
            $lead->setPhone($indicative . rand(10000000, 99999999));
            $lead->setIsMobile($indicative == 9 ? 1: 0);
            $lead->setInOpposition(0);

            for($j = 0; $j < 2; $j++){
                $contact = new Contact();
                $contact->setSubCategory($sub_categories[array_rand($sub_categories,1)]);
                $contact->setGender($genders[array_rand($genders,1)]);
                $contact->setParish($parishes[array_rand($parishes,1)]);
                $contact->setCounty($counties[array_rand($counties,1)]);
                $contact->setDistrict($districts[array_rand($districts,1)]);
                $contact->setCountry($countries[array_rand($countries,1)]);
                $contact->setOwner($owners[array_rand($owners,1)]);
                $contact->setLead($lead);
                $contact->setEmail(rand(10000000, 99999999) . '@test.com');
                $contact->setBirthdate(new \DateTime(('19' . rand(50, 95) . '-0' . rand(1, 9) . '-' . rand(10, 28))));
                $contact->setAddress("Rua " . rand(10000000, 99999999));
                $contact->setFirstname("Dont care");
                $contact->setLastname("Dont care again");
                $contact->setIpaddress("127.0.0.1");
                $contact->setPostalcode1(rand(1000, 9999));
                $contact->setPostalcode2(rand(100, 999));
                $contact->setSource($sources[array_rand($sources,1)]);

                $manager->persist($contact);
            }
            $manager->persist($lead);
        }

        $manager->flush();
    }


} 