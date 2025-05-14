<?php
namespace Request;

use Srv\Core;
use Schema\Opponent;
use Cls\Player;
use Srv\DB;
use PDO;

class refreshLeagueOpponents{
    
    public function __request($player){
				
        $opp = DB::sql("SELECT `user_id` FROM `character` WHERE `league_group_id` <= {$player->character->league_group_id} AND `league_group_id` >= 0 AND `level` <= {$player->character->level} AND `id`<>{$player->character->id} ORDER BY `league_group_id` DESC LIMIT 3")->fetchALL(PDO::FETCH_NUM);
        
        if(count($opp) < 2)
            $opp = DB::sql("SELECT `user_id` FROM `character` WHERE `id`<>{$player->character->id} ORDER BY `league_group_id` ASC LIMIT 3")->fetchALL(PDO::FETCH_NUM);
        
        shuffle ( $opp );
        
        $oppData = [];
        foreach($opp as $val){
            $o = Player::findByUserId($val[0]);
            $o->loadForDuel();
			$opEq = $o->getOnlyEquipedItems();
            $oppData[] = [
			"opponent" => [
				"id" => $o->character->id,
				"name" => $o->character->name,
				"gender" => $o->character->gender,
				"level" => $o->character->level,
				"stat_base_stamina" => $o->character->stat_base_stamina,
				"stat_base_strength" => $o->character->stat_base_strength,
				"stat_base_critical_rating" => $o->character->stat_base_critical_rating,
				"stat_base_dodge_rating" => $o->character->stat_base_dodge_rating,
				"stat_total_stamina" => $o->character->stat_total_stamina,
				"stat_total_strength" => $o->character->stat_total_strength,
				"stat_total_critical_rating" => $o->character->stat_total_critical_rating,
				"stat_total_dodge_rating" => $o->character->stat_total_dodge_rating,
				"stat_weapon_damage" => $o->character->stat_weapon_damage,
				"honor" => $o->character->honor,
				"league_points" => $o->character->league_points,
				"league_group_id" => $o->character->league_group_id,
				"online_status" => $o->character->online_status,
				"appearance_skin_color" => $o->character->appearance_skin_color,
				"appearance_hair_color" => $o->character->appearance_hair_color,
				"appearance_hair_type" => $o->character->appearance_hair_type,
				"appearance_head_type" => $o->character->appearance_head_type,
				"appearance_eyes_type" => $o->character->appearance_eyes_type,
				"appearance_eyebrows_type" => $o->character->appearance_eyebrows_type,
				"appearance_nose_type" => $o->character->appearance_nose_type,
				"appearance_mouth_type" => $o->character->appearance_mouth_type,
				"appearance_facial_hair_type" => $o->character->appearance_facial_hair_type,
				"appearance_decoration_type" => $o->character->appearance_decoration_type,
				"show_mask" => $o->character->show_mask
			],
			"opponent_inventory" => $opEq['inventory'],
			"opponent_inventory_items" => $opEq['items']
            ];
        }
        
        Core::req()->data = array(
			'user'=>$player->user,
            'character'=>$player->character,
            'league_opponents'=>$oppData
        );
    }
    
}