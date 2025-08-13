<?php

namespace App\Service;

use App\Entity\Race;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class GenericHelper {

  public function __construct(
  ) {

  }

  /**
   * Confirm that a given Uuid matches a specific race.
   *
   * @param \App\Entity\Race $race
   * @param string $uuid
   *
   * @return void
   */
  public function validateRaceUuid(Race $race, string $uuid): void {
    if ($race->getUuid()->toString() !== $uuid) {
      throw new AccessDeniedException('UUID Mismatch');
    }
  }
}