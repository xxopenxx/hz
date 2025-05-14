<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Sidekicks;

class unbindSidekick{
    public function __request($player){
        $player->inventory->sidekick_id = 0;
        $player->sidekicks = Sidekicks::find(function($q) { $q->where('id', $player->inventory->sidekick_id); });
        $player->calculateStats();

        Core::req()->data = [
            'inventory'=>$player->inventory,
            'character'=>$player->character
        ];
    }
}   