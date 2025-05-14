<?php
namespace Request;

use Srv\Core;
class startHideoutTutorialFight{
    public function __request($player){
		
		Core::req()->data = array(
		    'character'=>[]
		);
    }
}