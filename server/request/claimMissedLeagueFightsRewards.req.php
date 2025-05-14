<?php
namespace Request;

use Srv\Core;

class claimMissedLeagueFightsRewards{
    public function __request($player){
        $missedleaguefights = $player->getMissedLeagueFights();
            Core::req()->data['missed_league_fights'] = $missedleaguefights;
    }
}