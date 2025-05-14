<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Sidekicks;

class releaseSidekick{
    public function __request($player){
        $sidekick_id = getField('sidekick_id', FIELD_NUM);
        $sidekick = Sidekicks::find(function($q) use($sidekick_id, $player) { $q->where('id', $sidekick_id)->where('character_id', $player->character->id); });
        
        if(!$sidekick)
            return Core::setError("errGetTheFuckOutOfHere");

        $sidekick_data = json_decode($player->inventory->sidekick_data)->orders;
        
        foreach ($sidekick_data as $key => $data) {
            if($data == $sidekick_id) array_splice($sidekick_data, $key, 1);//if the array value is equal to the sidekicks id, unset it since its going bye bye :(
        }

        $player->inventory->sidekick_data = json_encode(array("orders" => $sidekick_data));

        Sidekicks::delete(function($q)use($sidekick){ $q->where('id',$sidekick->id); });
        $player->inventory->sidekick_id = 0;

        Core::req()->data = [
            'sidekick'=>$sidekick,
            'inventory'=>$player->inventory
        ];
    }
}   