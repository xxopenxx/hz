<?php
namespace Request;

use Srv\Core;

class claimDuelRewards{
    
    public function __request($player){
        if($player->character->active_duel_id == 0)
            return Core::setError('errClaimDuelRewardsNoActiveDuel');
            
        $player->giveRewards($player->duel->character_a_rewards);
        
        $duelsCompleted = $player->getCurrentGoalValue('duels_completed');
        $player->updateCurrentGoalValue('duels_completed', $duelsCompleted + 1);

        if(($duelsCompleted + 1) == 2) {
            $player->updateCurrentGoalValue('second_duel_completed', 2);
        }

        $player->character->active_duel_id = 0;
        $player->duel->character_a_status = 3;
        
        if($player->inventory->sidekick_id)
            Core::req()->data['sidekick'] = $player->sidekicks;

        Core::req()->data = array(
            "user" => array(),
			"character" => $player->character,
			"duel" => [
				"id" => $player->duel->id,
				"character_a_status" => 3
			]
        );
        
        //TODO: remove missile item
        //if($player->getItemFromSlot('missiles_item_id') != null)
        //    Core::req()->data += array("items"=>array($player->getItemFromSlot('missiles_item_id')));
    }
}