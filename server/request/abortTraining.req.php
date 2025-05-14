<?php
namespace Request;

use Srv\Core;
use Cls\Utils;

class abortTraining{
    
    public function __request($player){
        if($player->character->active_training_id == 0)
            return Core::setError('noActiveTrain');
		
		$player->character->training_count += $player->training->iterations;
        $player->training->remove();
        $player->character->active_training_id = 0;
		
		Core::req()->data = array(
			"user" => array(),
			"character" => $player->character,
			'training'=>$player->training
		);
    }
}