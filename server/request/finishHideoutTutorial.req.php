<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\HideoutRoom;
use Cls\Utils;
use Cls\GameSettings;

class finishHideoutTutorial {
    public function __request($player) {

        /*
	"hideout_tutorial_finished_reward_glue" => 200,
	"hideout_tutorial_finished_reward_stone" => 400,
        */

        if (!$player->hideout) 
            return Core::setError('errHideoutNotFound');

        if (!$player->getTutorialFlag('hideout_first_attack'))
            return Core::setError('errHideoutTutorialNotStarted');

        if ($player->getTutorialFlag('hideout_tutorial_completed'))
            return Core::setError('errHideoutTutorialAlreadyFinished');

        $player->setTutorialFlag('hideout_tutorial_completed', true);

        $reward_glue = GameSettings::getConstant('hideout_tutorial_finished_reward_glue');
        $reward_stone = GameSettings::getConstant('hideout_tutorial_finished_reward_stone');

        $setRewards = [
            'glue' => min($player->hideout->max_resource_glue, $player->hideout->current_resource_glue + $reward_glue),
            'stone' => min($player->hideout->max_resource_stone, $player->hideout->current_resource_stone + $reward_stone)
        ];

		$player->hideout->current_resource_stone = $setRewards['stone'];
		$player->hideout->current_resource_glue = $setRewards['glue'];

        Core::req()->data = [
            'user'=>$player->user,
            'character'=>$player->character,
            'hideout' => $player->hideout
        ];			
    }
}
