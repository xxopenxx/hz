<?php
namespace Request;

use Srv\Core;

class getStandalonePaymentOffers{
    public function __request($player){
        if(($msgcount = $player->getUnreadedMessagesCount()) != 0)
            Core::req()->data['new_messages'] = $msgcount;
    }
}