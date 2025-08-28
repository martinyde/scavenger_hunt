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
     * @param array<string> $words list of words
     * @param int    $size  grid size
     * @param string $lang  language
     *
     * @throws \WordSearch\Exception
     */
    public static function create(array $words, int $size = 15, string $lang = 'en'): Puzzle
    {
        $alphabet = ('da' == $lang) ? new Danish() : null;
        $generator = new Generator($words, $size, $alphabet);

        return $generator->generate();
    }
}
