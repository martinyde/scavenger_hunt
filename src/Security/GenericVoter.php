<?php

namespace App\Security;

use App\Entity\ScavengerHunt;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @extends Voter<string, mixed>
 */
class GenericVoter extends Voter
{
    // these strings are just invented: you can use anything
    public const VIEW = 'view';
    public const ACCESS_ADMIN = 'access_admin';

    public function __construct(private readonly RoleHierarchyInterface $roleHierarchy)
    {
    }

  /**
   * @param string $attribute
   * @param mixed $subject
   *
   * @return bool
   */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::ACCESS_ADMIN])) {
            return false;
        }

        return true;
    }

  /**
   * @param string $attribute
   * @param mixed $subject
   * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
   * @param \Symfony\Component\Security\Core\Authorization\Voter\Vote|null $vote
   *
   * @return bool
   */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            $vote?->addReason('The user is not logged in.');

            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::ACCESS_ADMIN => $this->canAccessAdmin($subject, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(mixed $entity, User $user): bool
    {
        if ($this->hasRole($user, 'ROLE_ADMIN')) {
            return true;
        }

        if ($this->hasRole($user, 'ROLE_USER')) {
            if ($entity instanceof Task) {
                return $entity->getScavengerHunt()->getUser() === $user;
            }
            if ($entity instanceof ScavengerHunt) {
                return $entity->getUser() === $user;
            }

            return true;
        }

        return false;
    }

    private function canAccessAdmin(mixed $entity, User $user): bool
    {
        if ($this->hasRole($user, 'ROLE_ADMIN')) {
            return true;
        }

        return false;
    }

    private function hasRole(User $user, string $roleName): bool
    {
        $roles = $this->getUserRoles($user);

        return in_array($roleName, $roles);
    }

  /**
   * @return array<string>
   */
    private function getUserRoles(User $user): array
    {
        return $this->roleHierarchy->getReachableRoleNames($user->getRoles());
    }
}
