<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Cls\Utils;
use Cls\GameSettings;

class collectHideoutFightRewards {
    public function __request($player){
		Core::req()->data = [
		    'user'=>$player->user,
		    'character'=>$player->character,
			"hideout" =>$player->hideout,
		    "hideout_battle"=> [
                "id"=> 1,
                "attacker_status"=> 2
		    ],
		];
    }
}