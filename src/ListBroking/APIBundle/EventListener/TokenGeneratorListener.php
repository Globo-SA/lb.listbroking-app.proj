<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\APIBundle\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use ListBroking\APIBundle\Entity\APIToken;

class TokenGeneratorListener {
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof APIToken){
            $token = $entity->getToken();
            if (empty($token)) {
                $string = $this->genToken();
                $entity->setToken($string);
            }
        }
    }

    private function genToken(){
        $bytes = openssl_random_pseudo_bytes(100, $strong);
        if (true === $strong && false !== $bytes) {
            $randomData = $bytes;
        }

        if (empty($randomData)) { // Get 108 bytes of (pseudo-random, insecure) data
            $randomData = mt_rand() . mt_rand() . mt_rand() . uniqid(mt_rand(), true) . microtime(true) . uniqid(
                    mt_rand(),
                    true
                );
        }

        return rtrim(strtr(base64_encode(hash('sha256', $randomData)), '+/', '-_'), '=');
    }
}