<?php

header('content-type: text/plain;charset=utf-8');

function destroy_dir($dir) { 
    if (!is_dir($dir) || is_link($dir)) return unlink($dir); 
        foreach (scandir($dir) as $file) { 
            if ($file == '.' || $file == '..') continue; 
            if (!destroy_dir($dir . DIRECTORY_SEPARATOR . $file)) { 
                chmod($dir . DIRECTORY_SEPARATOR . $file, 0777); 
                if (!destroy_dir($dir . DIRECTORY_SEPARATOR . $file)) return false; 
            }; 
        } 
        return rmdir($dir); 
    } 


$cacheDir = realpath("../temp/cache");


if (is_dir($cacheDir)) {
	$status = destroy_dir($cacheDir);
	if ($status) {
		echo "Cache '".$cacheDir."' byla úspěšně smazána";
	} else {
		echo "Nastala chyba při mazání cache: '".$cacheDir."'";
	}
} else {
	echo "Cache v umístění '".$cacheDir."' neexistuje!";
}