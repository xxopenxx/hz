<?php
namespace Request;
use Srv\Core;
use Srv\Config;
use Schema\Guild;
use Schema\GuildBattle;
use Srv\DB;
use Cls\Player;
use PDO;

class getGuildBattleHistoryFights{
    public function __request($player){	
		//$attack = GuildBattle::findAll(function($q)use($player){ $q->where('guild_attacker_id', $player->guild->id); });		
		
		$attack = DB::sql("SELECT * FROM guild_battle WHERE guild_attacker_id = {$player->guild->id} OR guild_defender_id = {$player->guild->id}")->fetchALL(PDO::FETCH_ASSOC);
		
		//var_dump($attack);
		shuffle ($attack);	
		
		$attackData = [];		
		
		foreach($attack as $val){	
		
			$won = $val['guild_winner_id'] == $player->guild->id ? true : false;
			$ids = $val['guild_attacker_id'] == $player->guild->id ? $val['attacker_character_ids'] : $val['defender_character_ids'];
			$rewards = $val['guild_attacker_id'] == $player->guild->id ? $val['attacker_rewards'] : $val['defender_rewards'];
			$przeciwnik = $val['guild_attacker_id'] != $player->guild->id ? $val['guild_attacker_id'] : $val['guild_defender_id'];
			$guild = Guild::find(function($q)use($przeciwnik){ $q->where('id', $przeciwnik); });	
				//var_dump($guild);

            $attackData[] = [
				"type" => 2,
				"id" => $guild->id,
				"battle_timestamp" => $val['ts_attack'],
				"enemy" => $guild->id,
				"won" => $won,
				"joined_character_ids" => $ids,
				"rewards" => $rewards,
				"enemy_name" => $guild->name,
				"ebs" => $guild->emblem_background_shape,
				"ebc" => $guild->emblem_background_color,
				"ebbc" => $guild->emblem_background_border_color,
				"eis" => $guild->emblem_icon_shape,
				"eic" => $guild->emblem_icon_color,
				"eiz" => $guild->emblem_icon_size,
            ];			
		}
		
		Core::req()->data = array(
			'user'=>$player->user,
		    'character'=>$player->character,
			'guild_history_battles'=>$attackData
		);
		
    }
}