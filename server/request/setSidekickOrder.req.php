<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Sidekicks;

class setSidekickOrder{
    public function __request($player){
        $sidekick_id = getField('sidekick_id', FIELD_NUM);
        $move_up = getField('move_up', FIELD_BOOL);
        $move_to_end = getField('move_to_end', FIELD_BOOL);
        $original_place = 0;
        $designated_place = 0;
        $insert_all = 0;

        $sidekicks = Sidekicks::findAll(function($q) use($player) { $q->where('character_id', $player->character->id); });

        if(!$sidekicks)
            return Core::setError("errGetTheFuckOutOfHere");

        $sidekick_data = json_decode($player->inventory->sidekick_data)->orders; //if there is order data already, just parse it

        foreach ($sidekick_data as $key => $data) {
            if($data == $sidekick_id) $original_place = $key; //if the array value is equal to the sidekicks id, set the original place variable to the key
        }

        $data_length = count($sidekick_data) - 1;

        if($move_up == "true"){ //if move up true, move up
            $designated_place = $original_place - 1;
            if($move_to_end == "true") $designated_place = 0; //move to the absolute top  
        } else if($move_up == "false") { //if move up false, move down
            $designated_place = $original_place + 1;
            if($move_to_end == "true") $designated_place = $data_length; //move to the absolute bottom
        }
        
        if($designated_place < 0) $designated_place = 0;
        else if($designated_place > $data_length) $designated_place = $data_length;
        
        moveElement($sidekick_data, $original_place, $designated_place); //1 arg = array, 2 arg = what elem to move, 3 arg = where to move it

        $player->inventory->sidekick_data = json_encode(array("orders" => $sidekick_data));

        Core::req()->data = [
            'inventory'=>$player->inventory
        ];
    }
}