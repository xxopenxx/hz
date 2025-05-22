<?php
namespace Cls;

use Cls\GameSettings;

use Schema\Hideout;
use Schema\HideoutRoom;

class HideoutCls extends Entity {
	public $hideout = null;
	public $hideout_room = null;
	public $hideout_rooms = [];

    public function loadHideout($player) {
		if(!$this->hideout)
			$this->hideout = Hideout::find(function($q) use ($player) { $q->where('character_id', $player->character->id); });

		if(!$this->hideout_room && $this->hideout)
			$this->hideout_room = HideoutRoom::find(function($q) { $q->where('hideout_id', $this->hideout->id); });

		if(!$this->hideout_rooms && $this->hideout)
			$this->hideout_rooms = HideoutRoom::findAll(function($q) { $q->where('hideout_id', $this->hideout->id); });
    }
}