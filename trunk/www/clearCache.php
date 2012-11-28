<?php

$cacheDir = realpath("../temp/cache");

if (is_dir($cacheDir)) {
	$status = @rmdir($cacheDir);
	if ($status) {
		echo "Cache '".$cacheDir."' byla úspěšně smazána";
	} else {
		echo "Nastala chyba při mazání cache: '".$cacheDir."'";
	}
}