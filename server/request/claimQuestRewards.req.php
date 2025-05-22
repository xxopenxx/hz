<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Battle;

class claimQuestRewards{
    
    public function __request($player){
        if($player->character->active_quest_id == 0)
            return Core::setError('errStartQuestActiveQuestFound');
        
        $quest_id = $player->character->active_quest_id;
        $quest = $player->getQuestById($quest_id);
        if($quest == null)
            return Core::setError('errNoActiveQuest');
        if($quest->status != 4)
            return Core::setError('errNoActiveQuest2');
        
        $create_new = toBool(getField('create_new', FIELD_BOOL));
        
        if($quest->fight_battle_id != 0)
            Battle::delete(function($q)use($quest){ $q->where('id',$quest->fight_battle_id); });

        $x = $quest->rewards;
        $player->giveRewards($quest->rewards);
        $quest->status = 5;
        
        if(!$player->getTutorialFlag('first_mission')){
            $player->setTutorialFlag('first_mission', true);
            $player->giveMoney(10);
        }

        $questsCompleted = $player->getCurrentGoalValue('quests_completed');
        $player->updateCurrentGoalValue('quests_completed', $questsCompleted + 1);

        if (($questsCompleted + 1) == 2) {
            $player->updateCurrentGoalValue('second_quests_completed', 2);
        }

        $player->generateQuestsAtStage($quest->stage, 3);

        Core::req()->data = array(
            "user" => $player->user,
            "character" => $player->character,
            "quests" => $player->quests,
            "inventory" => array()
            //"items" => array() Wtedy gdy nowa misja jest z itemem
        );
        if($player->inventory->sidekick_id)
            Core::req()->data['sidekick'] = $player->sidekicks;

        // dungeons
        $rew = json_decode($x, true);
        if (isset($rew['dungeon_key'])) {
            foreach ($player->dungeons as $dungeon) {
                if ($dungeon->identifier == $rew['dungeon_key']) {
                    $dungeon->status = 2;

                    // Goals
                    $dungeonUnlocked = $player->getCurrentGoalValue("{$dungeon->identifier}_unlocked");
                    if ($dungeonUnlocked == 0) {
                        $player->updateCurrentGoalValue("{$dungeon->identifier}_unlocked", 1);
                    }
                    break;
                }
            }
            Core::req()->data['dungeon'] = $dungeon;
        }
    }
}