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
        'Wow, you read the whole documentation! That\'s %s more energy for you.',
        'You just got back from SunshinePHP in sunny Miami. That\'s worth %s more energy!',
        'When do you sleep!? You read RESTful web APIs cover to cover: %s energy for you',
        'You went for a walk and the solution just came to you: %s more energy.',
        'You had 3 beers and hit your Ballmer Peak: %s more energy!',
        'You went for a jog and thought of the answer! %s more energy',
        'You remembered to feed your cat! %s energy',
        'Congrats - you created the newest InstaFaceTweet, that\'s %s more energy!',
        'Your investor check arrived %s energy',
        'Your internet went down and productivity is way way up! %s energy',
        'Attended a geek breakfast - food in your belly means %s energy!',
        'Working from home = no meetings! %s energy',
        'Free donuts in the breakroom: %s energy',
        'Client check arrive in the mail. ChaChing %s',
        'Built a custom standup desk: %s energies!',
        'Your tests pass! %s energy',
        'You make bootstrap look good, %s energy',
    );

    private static $negativeMessages = array(
        'You *meant* to read something, but watched re-runs of Star Trek instead. Awesome, but that\'ll cost %s energy',
        'You fell asleep at the office while reading the docs and your co-workers think you\'re kinda weird: %s energy',
        'Drank too much Red Bull and ran laps around the office instead of watching a screencast: %s energy',
        'You drank past your Ballmer Peak and are now sleeping: %s energy',
        'Bummer, you caught the flu %s energy',
        'Your cat spilled coffee on your keyboard %s energy',
        'Womp womp, you don\'t have a solid state harddrive that\'s %s energy.',
        'A new wearable is available and productivity is down: %s energy.',
        'Hand cramp! %s energy',
        'You cut out early for happy hour: %s energy',
        'Client asks you to make the logo bigger, %s energy',
        'Your cat got caught in the blinds %s energy',
        'The dinosaurs...they know how to open doors! %s energy',
        
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

            $message = sprintf(self::$positiveMessages[$key], $powerChange);
        } else {
            $key = array_rand(self::$negativeMessages);

            $message = sprintf(self::$negativeMessages[$key], $powerChange);
        }

        return array(
            'message' => $message,
            'powerChange' => $powerChange,
        );
    }
} 