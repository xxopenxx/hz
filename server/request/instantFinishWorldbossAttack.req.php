<?php
namespace Request;
use Srv\Core;
use Srv\Config;

class instantFinishWorldbossAttack {
    public function __request($player) {
        $attackId = $player->character->active_worldboss_attack_id;
        if ($attackId == 0)
            return Core::setError('errNoActiveWorldbossAttack');
        $attack = $player->getWorldbossAttackById($attackId);
        if (!$attack)
            return Core::setError('errAttackNotFound');
        $event = $player->getActiveWorldbossEvent();
        if (!$event || $event->id != $attack->worldboss_event_id)
            return Core::setError('errEventMismatch');
        $cost = Config::get('constants.worldboss_attack_instant_cost');
        if ($player->user->premium_currency < $cost)
            return Core::setError('errNotEnoughPremium');
        $player->user->premium_currency -= $cost;
        $attack->ts_complete = time();
        $attack->save();
        // Przekieruj do checkForWorldbossAttackComplete
        $req = new \Request\checkForWorldbossAttackComplete();
        $req->__request($player);
    }
} 
