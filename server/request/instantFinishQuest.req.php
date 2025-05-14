<?php
namespace Request;

use Srv\Core;

class instantFinishQuest{
    
    public function __request($player){
        if($player->character->active_quest_id == 0)
            return Core::setError('errStartQuestActiveQuestFound');
        
        $quest_id = $player->character->active_quest_id;
        $quest = $player->getQuestById($quest_id);
        if($quest == null)
            return Core::setError('errNoActiveQuest');
        if($quest->status != 2)
            return Core::setError('errNoActiveQuest2');
        
        //$skipCost = Utils::getQuestInstantFinishCost($quest->duration_raw);
		if($quest->duration_raw <= 450){
			$skipCost = 1;
		} elseif($quest->duration_raw > 450 && $quest->duration_raw <= 750){
			$skipCost = 2;
		} elseif($quest->duration_raw > 750 && $quest->duration_raw <= 1050){
			$skipCost = 3;
		} else {
			$skipCost = 4;
		}
		if($player->user->premium_currency < $skipCost)
			return Core::setError("errRemovePremiumCurrencyNotEnough");
		
		$player->givePremium(-$skipCost);
		$quest->ts_complete = 0;
		
		Core::req()->data = array(
		    'character'=>$player->character,
		    'quest'=>array('id'=>$quest->id, 'ts_complete'=>$quest->ts_complete)
		);
    }
}