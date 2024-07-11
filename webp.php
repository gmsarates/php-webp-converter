<?php
    require(__DIR__ . '/vendor/autoload.php');

    use Gmsarates\Webp\Converter;

	$webp = new Converter();
	$webp->init($argv);

?>