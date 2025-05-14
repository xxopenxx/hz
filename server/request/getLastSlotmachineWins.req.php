<?php
namespace Request;

use Srv\Core;
use Cls\Bonus\SlotMachine;

class getLastSlotmachineWins{
    public function __request($player){
        $msgs = SlotMachine::getLastWins();

        Core::req()->data = [
            'messages'=>json_encode($msgs)
        ];
    }
}