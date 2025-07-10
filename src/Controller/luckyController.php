<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use WordSearch;


class luckyController extends AbstractController
{
  #[Route('/lucky/number')]
  public function number(): Response
  {
    $puzzle = WordSearch\Factory::create(['foo', 'bar', 'oooo'], 50 );
    $transformer = new WordSearch\Transformer\HtmlTransformer($puzzle);

    return $this->render('lucky/number.html.twig', [
      'grid' => $transformer->grid(),
      'wordlist' => $transformer->wordList()
    ]);
  }
}