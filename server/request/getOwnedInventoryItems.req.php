<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Cls\Utils;
use Cls\Utils\ItemsList;
use Cls\Utils\Item;

class getOwnedInventoryItems{
    
    public function __request($player){
			$itemtype = intval(getField('item_type', FIELD_NUM));
		   $itemquality = intval(getField('item_quality', FIELD_NUM));
		
			$item = ItemsList::$ITEMS[Item::$TYPE[$itemtype]][mt_rand(0, count(ItemsList::$ITEMS[Item::$TYPE[$itemtype]])-1)];
		
         Core::req()->data = array(
            "user"=>$player->user
        );
		
		Core::req()->data['owned_item_templates'] = array(
				$item['identifier']
		);
		
    }
}