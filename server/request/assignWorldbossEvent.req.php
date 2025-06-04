<?php
namespace Request;

use Srv\Core;
use Schema\WorldbossEvent;

class assignWorldbossEvent {
    public function __request($player) {
        $eventId = intval(getField('worldboss_event_id', FIELD_NUM));
        $event = WorldbossEvent::find(function($q) use ($eventId) {
            $q->where('id', $eventId);
        });
        if (!$event || $event->status != 1) {
            return Core::setError('errInvalidEvent');
        }

        $player->character->worldboss_event_id = $eventId;
        $player->character->worldboss_event_attack_count = 0;
        $player->character->active_worldboss_attack_id = 0;
        $player->character->current_stage = $event->stage;

        Core::req()->data = [
            'user' => $player->user,
            'character' => $player->character
        ];
    }
}
