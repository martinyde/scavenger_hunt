<?php

namespace App\Service;

use App\WordSearchAlphabet\Danish;
use WordSearch\Generator;
use WordSearch\Puzzle;

class WordSearchFactory
{
  /**
   * Create a puzzle.
   *
   * @param array $words List of words.
   * @param integer $size Grid size.
   * @param string $lang Language.
   *
   * @return \WordSearch\Puzzle
   * @throws \WordSearch\Exception
   */
  public static function create(array $words, int $size = 15, string $lang = 'en'): Puzzle {
    $alphabet = ($lang == 'da') ? new Danish() : null;
    $generator = new Generator($words, $size, $alphabet);

    return $generator->generate();
  }
}