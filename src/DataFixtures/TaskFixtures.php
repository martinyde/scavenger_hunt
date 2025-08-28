<?php

namespace App\DataFixtures;

use App\Entity\ScavengerHunt;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $clues = [
            [
                'title' => 'The Ancient Tree',
                'pass_key' => 'oak',
                'text_before' => 'I stand tall with branches wide, yet have no leaves to show. My rings tell stories of the past, what am I do you know?',
                'text_after' => 'Congratulations on finding the ancient landmark!',
                'solutions' => ['tree', 'oak tree', 'old tree', 'ancient tree'],
            ],
            [
                'title' => 'The Silent Guardian',
                'pass_key' => 'watcher',
                'text_before' => 'Look for me standing silent and still, watching over all without a sound.',
                'text_after' => 'I am made of stone but never speak, I watch but never blink, I guard but never sleep. What am I?',
                'solutions' => ['statue', 'monument', 'gargoyle', 'sculpture'],
            ],
            [
                'title' => 'The Time Keeper',
                'pass_key' => 'chronos',
                'text_before' => 'My face always shows the truth, my hands are always moving, yet I remain in one place. What am I?',
                'text_after' => 'Well done finding the historic timepiece!',
                'solutions' => ['clock', 'clock tower', 'sundial', 'watch'],
            ],
            [
                'title' => 'The Knowledge Well',
                'pass_key' => 'wisdom',
                'text_before' => 'Search for a place where words flow like water.',
                'text_after' => 'I hold countless stories but am not a book, I house wisdom but am not a sage, I welcome all but am not a home. What am I?',
                'solutions' => ['library', 'bookstore', 'archive', 'university'],
            ],
            [
                'title' => 'The Metal Bird',
                'pass_key' => 'iron',
                'text_before' => 'I soar without feathers, I fly without life, I\'m born of man\'s mind not nature\'s design. What am I?',
                'text_after' => 'You\'ve discovered the aviation monument!',
                'solutions' => ['airplane', 'aircraft', 'plane', 'jet'],
            ],
            [
                'title' => 'The Water Path',
                'pass_key' => 'aqua',
                'text_before' => 'Find where the water flows but never escapes its stone embrace.',
                'text_after' => 'I dance and bubble but never leave my home, I splash and play but never run free. What am I?',
                'solutions' => ['fountain', 'waterfall', 'stream', 'pond'],
            ],
            [
                'title' => 'The Light Tower',
                'pass_key' => 'beacon',
                'text_before' => 'I stand where land meets sea, my light guides those far from shore. What am I?',
                'text_after' => 'You\'ve found the coastal guardian!',
                'solutions' => ['lighthouse', 'beacon', 'light tower', 'sea light'],
            ],
            [
                'title' => 'The Underground Passage',
                'pass_key' => 'tunnel',
                'text_before' => 'Look for the path that goes under not over, where darkness prevails and echoes discover.',
                'text_after' => 'I have a mouth but never eat, I have a bed but never sleep. What am I?',
                'solutions' => ['tunnel', 'cave', 'river', 'subway'],
            ],
            [
                'title' => 'The Green Maze',
                'pass_key' => 'leafy',
                'text_before' => 'I confuse and bewilder with my living walls, many enter but struggle to exit my halls. What am I?',
                'text_after' => 'Well done navigating the verdant puzzle!',
                'solutions' => ['maze', 'labyrinth', 'hedge maze', 'garden maze'],
            ],
            [
                'title' => 'The Stone Bridge',
                'pass_key' => 'arch',
                'text_before' => 'Find the structure that joins what water divides.',
                'text_after' => 'I connect yet never touch, I arch but never bend, I serve but never speak. What am I?',
                'solutions' => ['bridge', 'stone bridge', 'arch bridge', 'viaduct'],
            ],
            [
                'title' => 'The Metal Giant',
                'pass_key' => 'steel',
                'text_before' => 'I reach for the sky with my metal frame, a monument to human achievement and fame. What am I?',
                'text_after' => 'You\'ve found the towering landmark!',
                'solutions' => ['tower', 'skyscraper', 'monument', 'antenna'],
            ],
            [
                'title' => 'The Frozen Mirror',
                'pass_key' => 'ice',
                'text_before' => 'Seek out where water sleeps in stillness, reflecting the world around it.',
                'text_after' => 'I mirror the sky without a frame, I\'m liquid yet still, I reflect without thought. What am I?',
                'solutions' => ['lake', 'pond', 'pool', 'reservoir'],
            ],
            [
                'title' => 'The Rising Steps',
                'pass_key' => 'path',
                'text_before' => 'I let you climb without using your hands, one level to the next across the land. What am I?',
                'text_after' => 'Congratulations on scaling the heights!',
                'solutions' => ['stairs', 'steps', 'staircase', 'ladder'],
            ],
            [
                'title' => 'The Whispering Forest',
                'pass_key' => 'woodland',
                'text_before' => 'Enter where many trunks stand tall and leaves create a ceiling for all.',
                'text_after' => 'I\'m full of life but not a city, I provide shelter but not a home, I have many branches but am not a bank. What am I?',
                'solutions' => ['forest', 'woods', 'grove', 'woodland'],
            ],
            [
                'title' => 'The Spinning Wheel',
                'pass_key' => 'circle',
                'text_before' => 'I turn and turn but never move forward, around and around without getting dizzy. What am I?',
                'text_after' => 'You\'ve discovered the circular attraction!',
                'solutions' => ['carousel', 'ferris wheel', 'windmill', 'waterwheel'],
            ],
            [
                'title' => 'The Eternal Flame',
                'pass_key' => 'light',
                'text_before' => 'Find where fire burns but is never extinguished, a symbol of remembrance that never fades.',
                'text_after' => 'I burn without wood, I live without breath, I die not with time. What am I?',
                'solutions' => ['memorial flame', 'eternal flame', 'monument light', 'remembrance fire'],
            ],
            [
                'title' => 'The Music Box',
                'pass_key' => 'melody',
                'text_before' => 'I hold notes but am not a book, I create music but am not an instrument, I showcase talent but am not a stage. What am I?',
                'text_after' => 'You\'ve found the acoustic wonder!',
                'solutions' => ['concert hall', 'music venue', 'opera house', 'amphitheater'],
            ],
            [
                'title' => 'The Iron Horse',
                'pass_key' => 'steed',
                'text_before' => 'Look for me where I rest on metal roads, once breathing steam but now standing still.',
                'text_after' => 'I once thundered across the land, iron wheels on iron bands, now I stand in silent memory. What am I?',
                'solutions' => ['train', 'locomotive', 'steam engine', 'railroad'],
            ],
            [
                'title' => 'The Stone Circle',
                'pass_key' => 'ring',
                'text_before' => 'I am a ring of standing stones, older than the oldest bones, aligned with stars of long ago. What am I?',
                'text_after' => 'Congratulations on finding the prehistoric monument!',
                'solutions' => ['stone circle', 'henge', 'megalith', 'monument'],
            ],
            [
                'title' => 'The Glass House',
                'pass_key' => 'crystal',
                'text_before' => 'Seek a place where plants grow protected from the cold, behind walls you can see through.',
                'text_after' => 'I\'m made of glass but not a window, I hold life but am not a zoo, I create warmth but am not a furnace. What am I?',
                'solutions' => ['greenhouse', 'conservatory', 'botanical garden', 'solarium'],
            ],
            [
                'title' => 'The Fallen Giant',
                'pass_key' => 'colossus',
                'text_before' => 'I once stood tall above all others, now I lie in rest, my rings telling my age. What am I?',
                'text_after' => 'You\'ve found the recumbent forest titan!',
                'solutions' => ['fallen tree', 'redwood', 'sequoia', 'old growth'],
            ],
            [
                'title' => 'The Desert Ship',
                'pass_key' => 'sailor',
                'text_before' => 'Find the creature that sails across sand seas without water, carrying treasures through the heat.',
                'text_after' => 'I have humps but am not a mountain, I carry goods but am not a truck, I cross deserts but need no roads. What am I?',
                'solutions' => ['camel', 'dromedary', 'caravan', 'desert transport'],
            ],
            [
                'title' => 'The Sky Mirror',
                'pass_key' => 'heaven',
                'text_before' => 'I reflect the clouds by day and stars by night, yet you can walk across my surface. What am I?',
                'text_after' => 'Well done finding the reflective expanse!',
                'solutions' => ['lake', 'pond', 'reflective pool', 'water body'],
            ],
            [
                'title' => 'The Golden Dome',
                'pass_key' => 'crown',
                'text_before' => 'Look for where the sun seems captured, atop a building proud and tall.',
                'text_after' => 'I shine without light of my own, I crown without being royalty, I inspire awe but speak no words. What am I?',
                'solutions' => ['dome', 'capitol', 'mosque', 'cathedral'],
            ],
            [
                'title' => 'The Metal Web',
                'pass_key' => 'lattice',
                'text_before' => 'I am made of iron and air, a lacework of metal reaching high. What am I?',
                'text_after' => 'You\'ve discovered the iconic lattice structure!',
                'solutions' => ['eiffel tower', 'radio tower', 'observation tower', 'metal structure'],
            ],
            [
                'title' => 'The Sleeping Volcano',
                'pass_key' => 'dormant',
                'text_before' => 'Find the mountain with a fiery heart that now slumbers in peace.',
                'text_after' => 'I once spewed fire and ash, now I rest with snow upon my peak. What am I?',
                'solutions' => ['volcano', 'mount', 'crater', 'caldera'],
            ],
            [
                'title' => 'The Marble Hall',
                'pass_key' => 'glass',
                'text_before' => 'I house history in stone and glass, treasures of ages past within my walls. What am I?',
                'text_after' => 'Congratulations on finding the cultural repository!',
                'solutions' => ['museum', 'gallery', 'exhibition hall', 'heritage center'],
            ],
            [
                'title' => 'The Sun Dial',
                'pass_key' => 'shadow',
                'text_before' => 'Search for where shadow tells the time without ticking or tocking.',
                'text_after' => 'I work only when the sun shines, no batteries needed for my design. What am I?',
                'solutions' => ['sundial', 'solar clock', 'shadow clock', 'gnomon'],
            ],
            [
                'title' => 'The Copper Lady',
                'pass_key' => 'sentinel',
                'text_before' => 'I stand in the harbor, torch raised high, once brown now green against the sky. What am I?',
                'text_after' => 'You\'ve found the iconic symbol of freedom!',
                'solutions' => ['statue of liberty', 'liberty', 'lady liberty', 'liberty statue'],
            ],
            [
                'title' => 'The Hidden Cave',
                'pass_key' => 'grotto',
                'text_before' => 'Find where water has carved a sanctuary within solid rock, hidden from casual eyes.',
                'text_after' => 'I form over eons drop by drop, my beauty grows in darkness, my treasures hang from ceiling to floor. What am I?',
                'solutions' => ['cave', 'cavern', 'grotto', 'limestone cave'],
            ],
            [
                'title' => 'The Paper Oracle',
                'pass_key' => 'knowledge',
                'text_before' => 'I hold the world\'s knowledge in ordered rows, silent until opened by curious minds. What am I?',
                'text_after' => 'Well done finding the temple of learning!',
                'solutions' => ['library', 'book collection', 'archive', 'repository'],
            ],
            [
                'title' => 'The Painted Canyon',
                'pass_key' => 'cliffs',
                'text_before' => 'Look for where nature has painted the rocks in layers of time.',
                'text_after' => 'I am carved by water but colored by minerals, deep but not dark, ancient but ever changing. What am I?',
                'solutions' => ['canyon', 'painted desert', 'colorful gorge', 'rock formation'],
            ],
            [
                'title' => 'The Hanging Gardens',
                'pass_key' => 'paradise',
                'text_before' => 'I grow where you would not expect, defying gravity with my greenery. What am I?',
                'text_after' => 'You\'ve discovered the verdant wonder!',
                'solutions' => ['hanging gardens', 'vertical garden', 'rooftop garden', 'terraced garden'],
            ],
            [
                'title' => 'The Crystal Cave',
                'pass_key' => 'chamber',
                'text_before' => 'Find where earth\'s treasures grow in darkness, sparkling when light finds them.',
                'text_after' => 'I grow but am not alive, I sparkle but am not a star, I hide underground but am not a secret. What am I?',
                'solutions' => ['crystal cave', 'geode', 'mineral deposit', 'gem mine'],
            ],
            [
                'title' => 'The Iron Dragon',
                'pass_key' => 'serpent',
                'text_before' => 'I twist and turn with metal scales, taking riders up and down at thrilling speeds. What am I?',
                'text_after' => 'Congratulations on finding the exhilarating attraction!',
                'solutions' => ['roller coaster', 'thrill ride', 'amusement ride', 'steel coaster'],
            ],
            [
                'title' => 'The Sand Castle',
                'pass_key' => 'fortress',
                'text_before' => 'Search where wind has built mountains grain by grain, ever shifting, ever growing.',
                'text_after' => 'I rise without foundation, I move without legs, I grow without rain. What am I?',
                'solutions' => ['sand dune', 'desert hill', 'sand formation', 'beach dune'],
            ],
            [
                'title' => 'The Stargazer\'s Dome',
                'pass_key' => 'cosmic',
                'text_before' => 'I house a giant eye that peers into the cosmos, revealing distant worlds. What am I?',
                'text_after' => 'You\'ve found the astronomical observatory!',
                'solutions' => ['observatory', 'telescope', 'planetarium', 'star dome'],
            ],
            [
                'title' => 'The Forgotten City',
                'pass_key' => 'ruins',
                'text_before' => 'Find where civilization once thrived but now lies in silent stone, reclaimed by nature.',
                'text_after' => 'I was bustling but now am quiet, I was mighty but now am crumbled, I tell stories but cannot speak. What am I?',
                'solutions' => ['ruins', 'ancient city', 'lost settlement', 'archaeological site'],
            ],
            [
                'title' => 'The Living Fossil',
                'pass_key' => 'survivor',
                'text_before' => 'I have remained unchanged for millions of years, a living window to prehistoric times. What am I?',
                'text_after' => 'Well done finding the biological time capsule!',
                'solutions' => ['coelacanth', 'ginkgo tree', 'horseshoe crab', 'nautilus'],
            ],
        ];

        foreach ($clues as $key => $clue) {
            $task = new Task();
            $task->setTitle($clue['title']);
            $task->setPassKey($clue['pass_key']);
            $task->setTextBefore($clue['text_before']);
            $task->setTextAfter($clue['text_after']);
            $task->setSolutions($clue['solutions']);
            $task->setScavengerHunt(
                $this->getReference(
                    $key > 20 ? 'scavenger-hunt:TreasureHunter' : 'scavenger-hunt:ClueSeeker',
                    ScavengerHunt::class
                )
            );

            $manager->persist($task);

            $this->setReference('task:'.$clue['pass_key'], $task);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ScavengerHuntFixtures::class,
        ];
    }
}
