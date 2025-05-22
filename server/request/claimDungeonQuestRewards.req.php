<?php

namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Battle;

class claimDungeonQuestRewards{
    
    public function __request($player){
        if($player->character->active_dungeon_quest_id == 0)
            return Core::setError('errStartQuestActiveQuestFound');
        
        $quest_id = $player->character->active_dungeon_quest_id;
        $quest = $player->getDungeonQuestById($quest_id);
        $dungeon = $player->getDungeonById($quest->dungeon_id);
        $dungeon_number = intval(explode("dungeon", $dungeon->identifier)[1]); //explode cuz we need to get the current dungeon number
        $battle = Battle::find(function($q)use($quest){ $q->where('id',$quest->battle_id); }); //find the current battle 

        if($quest == null)
            return Core::setError('errNoActiveQuest');
        if($quest->status != 3)
            return Core::setError('errNoActiveQuest2');
        

        $player->giveRewards($quest->rewards);
        
        $quest->status = 4;
        $player->character->active_dungeon_quest_id = 0;

        if($battle->winner == 'a') { //if player is winner
            $dungeon->progress_index++; //increase current dungeon level
            if($dungeon->progress_index > 9){ //if level > 9, aka dungeon is finished
                $dungeon->progress_index = 0; 
                if($dungeon->mode == 2) { // if player completed all dungeon modes, set dungeon to finished.
                    $dungeon->status = 4;
                    $dungeon->ts_last_complete = time();
                } else {
                    // dungeon1_normal_completed

                    $mode = $dungeon->mode == 0 ? 'normal' : 'hard';
                    $dungeonCompleted = $player->getCurrentGoalValue("{$dungeon->identifier}_{$mode}_completed");
                    if ($dungeonCompleted == 0) {
                        $player->updateCurrentGoalValue("{$dungeon->identifier}_{$mode}_completed", 1);
                    }

                    $dungeon->mode++; //increase mode (difficulty)
                }
            }

            if($dungeon->status != 4) { //if dungeon isnt closed yet, generate new dungeon quest 
                $dungeon->current_dungeon_quest_id = $player->generateQuestAtDungeon($dungeon->id, $dungeon_number, $dungeon->progress_index, $dungeon->mode);
            }
        } else {
            $dungeon->current_dungeon_quest_id = $player->generateQuestAtDungeon($dungeon->id, $dungeon_number, $dungeon->progress_index, $dungeon->mode);
            $player->character->ts_last_dungeon_quest_fail = time();
        }
        
        if($quest->battle_id != 0) {
            Battle::delete(function($q)use($quest){ $q->where('id',$quest->battle_id); });
        }


        Core::req()->data = array(
            "user" => $player->user,
            "character" => $player->character,
            "dungeons"=>$player->dungeons, //dungeons
            "dungeon_quests"=>$player->dungeon_quests, //dungeon quests
            "inventory" => array()
            //"items" => array() Wtedy gdy nowa misja jest z itemem
        );
        
        if($player->inventory->sidekick_id)
            Core::req()->data['sidekick'] = $player->sidekicks;
    }
}