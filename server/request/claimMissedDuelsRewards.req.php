<?php
namespace Request;

use Srv\Core;

class claimMissedDuelsRewards{
    public function __request($player){
        $missedduels = $player->getMissedDuels();
            Core::req()->data['missed_duels'] = $missedduels;
    }
}