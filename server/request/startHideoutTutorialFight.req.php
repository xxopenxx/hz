<?php
namespace Request;

use Srv\Core;
use Cls\GameSettings;
use Schema\HideoutBattle;

class startHideoutTutorialFight {
    public function __request($player) {

        Core::req()->data = array(
			"character" => $player->character,
			"hideout"=>[
			'id'=>$player->hideout->id,
			'current_attacker_units'=>$player->hideout->current_attacker_units,
			'ts_last_opponent_refresh'=> 0,
			'active_battle_id'=> 1,
			],
			"hideout_battle" => [
			"id"=> 1,
			"attacker_hideout_id"=> $player->hideout->id,
			"defender_hideout_id"=> -1,
			"attacker_status"=> 1,
			"defender_status"=> 1,
			"attacker_count"=> 1,
			"attacker_profiles"=> "{\"a\":{\"level\":1,\"stamina\":100,\"strength\":100,\"criticalrating\":10,\"dodgerating\":10,\"weapondamage\":0,\"damage_bonus\":100}}",
			"defender_count"=> 1,
			"defender_profiles"=> "{\"w\":{\"level\":1,\"stamina\":50,\"strength\":100,\"criticalrating\":10,\"dodgerating\":10,\"weapondamage\":0,\"damage_bonus\":100},\"d\":{\"level\":11,\"stamina\":1100,\"strength\":1100,\"criticalrating\":10,\"dodgerating\":10,\"weapondamage\":0,\"damage_bonus\":1100}}",
			"hideout_winner_id"=> $player->hideout->id,
            "rounds"=> "[{\"a\":\"w_0\",\"d\":\"a_0\",\"r\":2,\"v\":87},{\"a\":\"a_0\",\"d\":\"w_0\",\"r\":2,\"v\":85},{\"a\":\"w_0\",\"d\":\"a_0\",\"r\":2,\"v\":103},{\"a\":\"a_0\",\"d\":\"w_0\",\"r\":2,\"v\":126},{\"a\":\"w_0\",\"d\":\"a_0\",\"r\":2,\"v\":83},{\"a\":\"a_0\",\"d\":\"w_0\",\"r\":2,\"v\":86},{\"a\":\"w_0\",\"d\":\"a_0\",\"r\":2,\"v\":91},{\"a\":\"a_0\",\"d\":\"w_0\",\"r\":2,\"v\":73},{\"a\":\"w_0\",\"d\":\"a_0\",\"r\":2,\"v\":103},{\"a\":\"a_0\",\"d\":\"w_0\",\"r\":1},{\"a\":\"w_0\",\"d\":\"a_0\",\"r\":2,\"v\":101},{\"a\":\"a_0\",\"d\":\"w_0\",\"r\":2,\"v\":89},{\"a\":\"w_0\",\"d\":\"a_0\",\"r\":2,\"v\":102},{\"a\":\"a_0\",\"d\":\"w_0\",\"r\":2,\"v\":121}]",
			"attacker_rewards"=> "{\"coins\":0,\"xp\":0,\"honor\":0,\"premium\":0,\"statPoints\":0,\"item\":0,\"hideout_glue\":250,\"hideout_stone\":500}",
			"attacker_bonus_rewards"=> "",
			"defender_rewards"=> "",
			"destroyed_attacker"=> 0,
			"destroyed_defender"=> 1,
			"attacker_character_name"=> $player->character->name,
			"defender_character_name"=> "Professor"
		]
        ); 


    }
}