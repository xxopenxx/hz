<?php
namespace Request;

use Srv\Core;

class openDungeon{
    
    public function __request($player){
        $dungeon_id = intval(getField('dungeon_id', FIELD_NUM));
        $dungeon = $player->getDungeonByDungeonId($dungeon_id);
        $dungeon_number = intval(explode("dungeon", $dungeon->identifier)[1]); 

        if($dungeon->status > 2 || $dungeon->status < 2)
            return Core::setError('errOpenInvalidStatus');
        
		$dungeon->status = 3;

        $dungeon->current_dungeon_quest_id = $player->generateQuestAtDungeon($dungeon->id, $dungeon_number, 0, 0);

        Core::req()->data = array(
		    'character'=>$player->character,
            'dungeons'=>$player->dungeons,
            'dungeon_quests'=>$player->dungeon_quests
		);
    }
}