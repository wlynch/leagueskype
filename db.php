<?php

function db_get($key){
	$str = file_get_contents('db.txt');
	$retval = null;
	foreach(explode("\n",$str) as $line){
		$arr=explode(" ",$line);
		if ($arr[0] == $key){
			$retval = $arr[1];
		}
	}
	return $retval;
}

function db_put($key,$value){
	$file=fopen("db.txt","a");
	fwrite($file,$key);
	fwrite($file," ");
	fwrite($file,$value);
	fwrite($file,"\n");
	fclose($file);
}

?>
