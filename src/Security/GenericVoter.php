<?php

namespace App\Security;

use App\Entity\ScavengerHunt;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class GenericVoter extends Voter
{
  // these strings are just invented: you can use anything
  const VIEW = 'view';
  const ACCESS_ADMIN = 'access_admin';

  public function __construct(private readonly RoleHierarchyInterface $roleHierarchy)
  {
  }

  protected function supports(string $attribute, mixed $subject): bool
  {
    // if the attribute isn't one we support, return false
    if (!in_array($attribute, [self::VIEW, self::ACCESS_ADMIN])) {
      return false;
    }

    return true;
  }

  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
  {
    $user = $token->getUser();

    if (!$user instanceof User) {
      // the user must be logged in; if not, deny access
      $vote?->addReason('The user is not logged in.');
      return false;
    }

    return match($attribute) {
      self::VIEW => $this->canView($subject, $user),
      self::ACCESS_ADMIN => $this->canAccessAdmin($subject, $user),
      default => throw new \LogicException('This code should not be reached!')
    };
  }

  private function canView($entity, User $user): bool
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

  private function canAccessAdmin($entity, User $user): bool
  {
    if ($this->hasRole($user, 'ROLE_ADMIN')) {
      return true;
    }

    return false;
  }

  private function hasRole(User $user, $roleName): string {
    $roles = $this->getUserRoles($user);

    return in_array($roleName, $roles);
  }


  private function getUserRoles(User $user): array {
    return $this->roleHierarchy->getReachableRoleNames($user->getRoles());
  }
}