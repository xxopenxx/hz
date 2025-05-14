<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Cls\Utils;
use Cls\Utils\ItemsList;
use Cls\Utils\Item;

class sewInventoryItem{
    
    public function __request($player){
			$identifier = getField('target_identifier', FIELD_IDENTIFIER);
		    $item_id = getField('item_id', FIELD_NUM);
			$item = $player->getItemById($item_id);
			
			if($item == null)
				Core::setError("invItemId");
		
			$slot_name = $player->inventory->getSlotByItemId($item_id);
			
			$item_2 = $player->changeItemIdentifier($identifier, $item);
		
          Core::req()->data = array(
            "user"=>$player->user,
			"item" => [
				"id" => $item_2->id,
				"identifier" => $item_2->identifier
			]
        );
    }
}