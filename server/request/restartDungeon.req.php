<?php
namespace Request;

use Srv\Core;

class restartDungeon{
    
    public function __request($player){
        $dungeon_id = intval(getField('dungeon_id', FIELD_NUM));
        $dungeon = $player->getDungeonByDungeonId($dungeon_id);
        $dungeon_time = $dungeon->ts_last_complete + (86000 * 5);
        $dungeon_number = intval(explode("dungeon", $dungeon->identifier)[1]); 

        if($dungeon->status == 4 && time() > $dungeon_time){
    		$dungeon->status = 3;
            $dungeon->mode = 0;
            $dungeon->progress_index = 0;

            $dungeon->current_dungeon_quest_id = $player->generateQuestAtDungeon($dungeon->id, $dungeon_number, 0, 0);

            Core::req()->data = array(
    		    'character'=>$player->character,
                'dungeons'=>$player->dungeons,
                'dungeon_quests'=>$player->dungeon_quests
    		);
        } else {
            return Core::setError('errRestartDungeonActiveCooldown');
        }
    }
}