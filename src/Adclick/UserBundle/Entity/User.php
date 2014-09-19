<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace Adclick\UserBundle\Entity;

use Adclick\DoctrineBehaviorBundle\Behavior\BlameableEntityBehavior;
use Adclick\DoctrineBehaviorBundle\Behavior\TimestampableEntityBehavior;
use FOS\UserBundle\Model\User as BaseUser;

class User extends BaseUser
{
    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    /**
     * Test if a user has a role like sf 1.4 does
     *
     * @author Bruno Escudeiro <bruno.escudeiro@adclick.pt>
     * @param      $credentials
     * @param bool $use_and
     *
     * @return bool
     */
    public function hasCredential($credentials, $use_and = true)
    {
        if (empty($credential))
        {
            return true;
        }

        if ($this->isSuperAdmin())
        {
            return true;
        }

        if (null === ($roles = $this->getRoles()))
        {
            return false;
        }

        if (!is_array($credentials))
        {
            return in_array($credentials, $roles);
        }

        $test = false;

        foreach ($credentials as $credential)
        {
            $test = $this->hasCredential($credential, $use_and ? false : true);

            if ($use_and)
            {
                $test = $test ? false : true;
            }

            if ($test)
            {
                break;
            }
        }

        if ($use_and)
        {
            $test = $test ? false : true;
        }

        return $test;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
