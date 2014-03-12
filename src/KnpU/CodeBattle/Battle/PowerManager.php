<?php

namespace KnpU\CodeBattle\Battle;

use KnpU\CodeBattle\Model\Battle;
use KnpU\CodeBattle\Model\Programmer;
use KnpU\CodeBattle\Model\Project;
use KnpU\CodeBattle\Repository\BattleRepository;
use KnpU\CodeBattle\Repository\ProgrammerRepository;

class PowerManager
{
    private $programmerRepository;

    private static $positiveMessages = array(
        'Wow, you just read the documentation from beginning to end! That\'s %s more energy for you.',
        'You just got back from SunshinePHP in sunny Miami. That\'s worth %s more energy!',
        'When do you sleep!? You read RESTful web APIs cover to cover - %s energy for you',
        'You went for a walk and the solution just came to you - %s more energy.'
    );

    private static $negativeMessages = array(
        'You *meant* to read something, but watched re-runs of Star Trek instead. Awesome, but that\'ll cost you %s energy',
        'You fell asleep at the office while trying to read the docs and your co-workers think your kinda weird: %s energy',
        'Drank too much Red Bull and ran laps around the office instead of watching a screencast: %s energy',
    );

    public function __construct(ProgrammerRepository $programmerRepository)
    {
        $this->programmerRepository = $programmerRepository;
    }

    /**
     * Powers up this programmer
     *
     * @param Programmer $programmer
     * @return string A description of what happened
     */
    public function powerUp(Programmer $programmer)
    {
        // vary the power change between 3 and 7
        $powerChange = rand(3, 7);
        // have a 1/3 chance that the change will be negative (and then make the negatives smaller)
        $powerChange = (rand(0, 2) == 2) ? (floor($powerChange/2) * -1) : $powerChange;

        $programmer->powerLevel = $programmer->powerLevel + $powerChange;
        $this->programmerRepository->save($programmer);

        if ($powerChange > 0) {
            $key = array_rand(self::$positiveMessages);

            return sprintf(self::$positiveMessages[$key], $powerChange);
        } else {
            $key = array_rand(self::$negativeMessages);

            return sprintf(self::$negativeMessages[$key], $powerChange);
        }
    }
} 