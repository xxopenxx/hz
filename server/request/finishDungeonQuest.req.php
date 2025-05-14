<?php
namespace Request;

use Srv\Core;
use Srv\Config;

class finishDungeonQuest{
    
    public function __request($player){
        if($player->character->active_dungeon_quest_id == 0)
            return Core::setError('errStartQuestActiveQuestFound');
        
        $quest_id = $player->character->active_dungeon_quest_id;
        $dungeon_quest = $player->getDungeonQuestById($quest_id);
        $dungeon = $player->getDungeonById($dungeon_quest->dungeon_id);

        if($dungeon_quest == null)
            return Core::setError('errNoActiveQuest');

        //exit(var_dump($battle));

        $dungeon_quest->status = 3;

        Core::req()->data = array(
            'character'=>$player->character,
            'dungeon_quest'=>$dungeon_quest
        );
    }
}