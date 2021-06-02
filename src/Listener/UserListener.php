<?php

namespace App\Listener;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Voir :
 * https://symfony.com/doc/current/bundles/DoctrineBundle/entity-listeners.html
 * https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#entity-listeners
 */
class UserListener
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function prePersist(User $user)
    {
        $this->updatePassword($user);
    }

    public function preUpdate(User $user)
    {
        $this->updatePassword($user);
    }

    private function updatePassword(User $user): bool
    {
        if (empty($plain = $user->getPlainPassword())) {
            return false;
        }

        $encoded = $this->encoder->encodePassword($user, $plain);

        $user->setPassword($encoded);

        return true;
    }
}
