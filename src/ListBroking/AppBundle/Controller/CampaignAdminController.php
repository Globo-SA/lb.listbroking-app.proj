<?php

/**
 *
 * @author     Diogo Basto <diogo.basto@smark.io>
 * @copyright  2017 Adclick
 * @license    [LISTBROKING_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;

class CampaignAdminController extends CRUDController{

    /**
     * @inheritdoc
     */
    public function editAction($id = null)
    {
        return parent::editAction();
    }

}