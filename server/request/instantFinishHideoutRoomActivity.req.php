<?php
		namespace Request;
		use Request\loginUser;
		
class instantFinishHideoutRoomActivity{

		public function __request($player){
				(new checkHideoutRoomActivityFinished())->__request($player, 1);
		}	
}