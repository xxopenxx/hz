<?php
namespace Request;

use Srv\Core;
use Srv\Config;
use Schema\Sidekicks;

class renameSidekick{
    public function __request($player){
        $sidekick_id = getField('sidekick_id', FIELD_NUM);
        $sidekick = Sidekicks::find(function($q) use($sidekick_id, $player) { $q->where('id', $sidekick_id)->where('character_id', $player->character->id); });
        
        if(!$sidekick)
            return Core::setError("errGetTheFuckOutOfHere");

        $name = getField('name', FIELD_ALNUM);
        $name = trim(strip_tags($name));
        
        if(!$name || strlen($name) < Config::get('constants.guild_name_length_min') || strlen($name) > Config::get('constants.guild_name_length_max'))
			return Core::setError("errRenameInvalidName");
        
        if($sidekick->name == $name)
		    return Core::setError("errRenameSameName");
        
        $cost = Config::get('constants.sidekick_rename_premium_currency_amount');
        if($player->getPremium() < $cost)
            return Core::setError('errRemovePremiumCurrencyNotEnough');
        
        $player->givePremium(-$cost);
        $sidekick->name = $name;
        
        Core::req()->data = [
            'sidekick'=>$sidekick
        ];
    }
}