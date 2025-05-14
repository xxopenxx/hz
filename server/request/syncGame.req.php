<?php
namespace Request;

use Srv\Core;

class syncGame{
    public function __request($player){
        if(($msgcount = $player->getUnreadedMessagesCount()) != 0)
            Core::req()->data['new_messages'] = $msgcount;
        if(($missedduels = $player->getMissedDuels()) != 0)
            Core::req()->data['missed_duels'] = $missedduels;
        if(($missedleaguefights = $player->getMissedLeagueFights()) != 0)
            Core::req()->data['missed_league_fights'] = $missedleaguefights;
    }
}