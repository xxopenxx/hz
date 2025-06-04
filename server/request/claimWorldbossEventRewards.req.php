<?php
namespace Request;

use Srv\Core;
use Schema\WorldbossEvent;
use Schema\WorldbossAttack;

class claimWorldbossEventRewards {
    public function __request($player) {
        $eventId = intval(getField('worldboss_event_id', FIELD_NUM));
        $discardItem = toBool(getField('discard_item', FIELD_BOOL));

        if ($player->character->worldboss_event_id != $eventId) {
            return Core::setError('errEventMismatch');
        }

        $event = WorldbossEvent::find(function($q) use ($eventId) {
            $q->where('id', $eventId);
        });
        if (!$event) {
            return Core::setError('errInvalidEvent');
        }
        if ($event->status == 1) {
            return Core::setError('errEventRunning');
        }

        // Mark event as completed for the character
        $player->character->worldboss_event_id = 0;
        $player->character->active_worldboss_attack_id = 0;

        $event->status = 4; // finished
        $event->save();

        Core::req()->data = [
            'user' => $player->user,
            'character' => $player->character
        ];
    }
}
