<?php
namespace Schema;

use Srv\Record;
use JsonSerializable;

class GuildDungeonBattle extends Record{
    protected static $_TABLE = 'guild_dungeon_battle';

    public function getCharacterIDS(){
        return json_decode($this->character_ids,TRUE);
    }

    public function getGuildDungeon(){
        if($this->status == 2)
            return array_merge($this->getData(['id','battle_time','ts_attack','ts_attack','npc_team_identifier','npc_team_character_profiles','status','settings','character_ids']), ['character_ids'=>$this->character_ids]);
        return array_merge($this->getData(), ['character_ids'=>$this->character_ids]);
    }	
	
    public function getGuildDungeonFinished(){
        if($this->status == 2)
			return $this->id;
    }		
	
    public function addPlayerToDungeonBattle($player){
        $players = json_decode($this->character_ids, true);
        if(in_array($player->character->id, $players))
            return FALSE;
        $players[] = $player->character->id;
        $this->character_ids = json_encode(array_values($players));
        return TRUE;
    }	
	
    protected static $_FIELDS = [
        'id'=>0,
        'status'=>0,
        'battle_time'=>0,
        'ts_attack'=>0,
        'guild_id'=>0,
        'ts_unlock'=>0,
        'npc_team_identifier'=>'',
        'settings'=>'',
        'character_ids'=>'',
        'joined_character_profiles'=>'',
        'npc_team_character_profiles'=>'',
        'rounds'=>'',
        'rewards'=>'',
        'initiator_character_id'=>0,
    ];
}