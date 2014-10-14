<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\UIBundle\Form;


use ListBroking\CoreBundle\Service\CoreService;
use ListBroking\ExtractionBundle\Entity\Extraction;
use ListBroking\LeadBundle\Service\ContactDetailsService;
use ListBroking\LeadBundle\Service\LeadService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FiltersForm extends AbstractType
{

    private $uniqid;

    private $filters;
    /**
     * @var CoreService
     */
    private $c_service;
    /**
     * @var ContactDetailsService
     */
    private $cd_service;

    function __construct(CoreService $c_service, ContactDetailsService $cd_service)
    {
        // An UniqueID is appended to the
        // name to avoid form collisions
        $this->uniqid = uniqid();
        $this->c_service = $c_service;
        $this->cd_service = $cd_service;

        $this->filters = array(
            array(
                'name' => 'gender',
                'type' => 'choice',
                'choices' => $this->cd_service->
            )
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $filters = $this->extraction['filters'];

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'campaign_' . $this->uniqid;
    }
}
