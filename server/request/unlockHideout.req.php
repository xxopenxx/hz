<?php
namespace Request;

use Srv\Core;
use Cls\GameSettings;
use Schema\Hideout;
use Cls\Utils;

class unlockHideout{
    
    public function __request($player){
		$hideout_unlock_min_level = GameSettings::getConstant("hideout_unlock_min_level");

		if($player->character->level < $hideout_unlock_min_level)
			return Core::setError('failed');
		
		if($player->hideout)
			return Core::setError('failed');

        //Create hideout
        $hideout = new Hideout([
            'character_id'=>$player->character->id
        ]);

        $hideout->save();
        $player->hideout = $hideout;
		
		Core::req()->data = [
		    'user'=>$player->user,
		    'character'=>$player->character,
		    'hideout'=>$player->hideout
		];
    }
}