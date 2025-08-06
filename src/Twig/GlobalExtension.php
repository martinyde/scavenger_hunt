<?php

namespace App\Twig;

use App\Entity\Race;
use Symfony\Component\Clock\DatePoint;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use WordSearch;

class GlobalExtension extends AbstractExtension
{
  public function getFunctions(): array
  {
    return [
      new TwigFunction('global_data', [$this, 'getGlobalData']),
    ];
  }

  public function getGlobalData(object $entity): array
  {
    if ($entity instanceof Race) {
      $data = [];

      // Add Word search
      $passKeys = [];
      $size = 15;
      foreach ($entity->getScavengerHunt()->getTasks() as $task) {
        $passkey = $task->getPassKey();
        $size = max(strlen($passkey), $size);
        $passKeys[] = $task->getPassKey();
      }
      try {
        $puzzle = WordSearch\Factory::create(
          $passKeys,
          $size,
        );
      }
      catch (\Exception $e) {
        $puzzle = null;
      }

      $data['puzzle'] = new WordSearch\Transformer\HtmlTransformer($puzzle);

      // Add timer
      $now = new DatePoint();
      $data['timer'] = [
        'secondsLeft' => $entity->getTimeStart()->modify('+' . $entity->getRaceDuration() . ' seconds')->getTimestamp() - $now->getTimestamp(),
        'duration' => $entity->getRaceDuration(),
        'raceState' => $entity->isActive()
      ];
      return $data;
    }

    return [];
  }
}
