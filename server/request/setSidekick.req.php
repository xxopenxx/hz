<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Sidekicks;

class setSidekick{
    public function __request($player){
        $sidekick_id = getField('sidekick_id', FIELD_NUM);
        $sidekick = Sidekicks::find(function($q) use($sidekick_id, $player) { $q->where('id', $sidekick_id)->where('character_id', $player->character->id); });
        
        if(!$sidekick)
            return Core::setError("errGetTheFuckOutOfHere");

        $player->inventory->sidekick_id = intval($sidekick->id);
        $player->sidekicks = $sidekick;
        $player->calculateStats();
        
        Core::req()->data = [
            'character'=>$player->character,
            'inventory'=>$player->inventory,
            'sidekick'=>$sidekick
        ];
    }
}