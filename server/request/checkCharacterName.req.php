<?php
namespace Request;

use Srv\Core;
use Srv\DB;

class checkCharacterName{
    
    public function __request(){
        $name = getField('name', FIELD_ALNUM);
        if(!$name || strlen($name) < 3 || strlen($name) > 25 || is_numeric($name))
            return Core::setError('errCheckCharacterNameInvalidName');
        
        $exts = DB::table('character')->select()->where('name', 'LIKE', '%'.$name.'%')->count();
        
        if($exts)
            Core::req()->data = (['available'=>false,'alternative'=>($name.$exts)]);
        else
            Core::req()->data = (['available'=>true]);
    }
}