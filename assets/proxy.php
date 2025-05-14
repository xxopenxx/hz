<?php

if(!isset($_GET['source']) || empty($_GET['source']))
    exit();

$file = $_GET['source'];

//$file_path = explode('?', $file)[0];
$file_path = trim($file, '/');
$file_path = explode('?',$file_path)[0];

$SAVE_PATH = 'save/%s';
$FILE_PATH = sprintf($SAVE_PATH, $file_path);


if(!file_exists($FILE_PATH)) {	
    $ch = curl_init('http://hz-static-2.akamaized.net/'.$file_path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $data = curl_exec($ch);
	$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	$contentLeng = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($ch);
    
	header('x-readed: nie');
	header('Content-Length: '.$contentLeng);
	header('Content-Type: '.$contentType);
	print($data);
	
    if (!is_dir($FILE_PATH)) {
        $folder = substr($FILE_PATH, 0, strrpos($FILE_PATH, '/'));
        createDirectories($folder);
	}
    file_put_contents($FILE_PATH, $data);	
	
} else {
	header('x-readed: tak');
	header('Content-Length: '.filesize($FILE_PATH));
	header('Content-Type: '.mime_content_type ($FILE_PATH));
    readfile($FILE_PATH);	
}

function createDirectories($path){
    $folders = explode('/', $path);
    for($i=0,$c=count($folders); $i < $c; $i++){
        $folcopy = [];
        $folcopy = array_slice ($folders, 0, $i+1);
        if(!is_dir(join('/', $folcopy)))
            mkdir(join('/', $folcopy));
    }
}

?>