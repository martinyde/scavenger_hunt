<?php

namespace App\Twig;

use App\Entity\Race;
use App\Service\GenericHelper;
use App\Service\WordSearchFactory;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use WordSearch;

class GlobalExtension extends AbstractExtension
{

  public function __construct(
    protected RequestStack $request,
    protected GenericHelper $genericHelper,
  ) {
  }

  public function getFunctions(): array
  {
    return [
      new TwigFunction('global_data', [$this, 'getGlobalData']),
    ];
  }

  public function getGlobalData(object $entity): array
  {
    $data = [];

    if ($entity instanceof Race) {
      // Add Word search
      $passKeys = [];
      $size = 15;
      foreach ($entity->getScavengerHunt()->getTasks() as $task) {
        $passkey = $task->getPassKey();
        $size = max(strlen($passkey), $size);
        $passKeys[] = $task->getPassKey();
      }
      try {
        $puzzle = WordSearchFactory::create(
          $passKeys,
          $size,
          'da'
        );
      }
      catch (\Exception $e) {
        $puzzle = null;
      }

      $data['puzzle'] = new WordSearch\Transformer\HtmlTransformer($puzzle);

      // Add timer
      $now = new DatePoint();
      $data['timer'] = [
        'secondsLeft' => $entity->getTimeStart() ? $entity->getTimeStart()->modify('+' . $entity->getRaceDuration() . ' seconds')->getTimestamp() - $now->getTimestamp(): $entity->getRaceDuration(),
        'duration' => $entity->getRaceDuration(),
        'raceState' => $entity->isActive()
      ];
      return $data;
    }

    return [];
  }
}
