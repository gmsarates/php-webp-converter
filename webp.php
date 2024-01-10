<?php
	require(__DIR__ . '/src/Console.php');
	require(__DIR__ . '/src/Converter.php');

	$webp = new Converter();
	$webp->init($argv);

?>