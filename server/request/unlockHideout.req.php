<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Hideout;
use Cls\Utils;

class unlockHideout{
    
    public function __request($player){

		if($player->character->level < 8)
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