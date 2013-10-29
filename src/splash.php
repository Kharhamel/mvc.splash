<?php
// First thing first, let's include the Mouf configuration:
// (only if we are not in admin mode)
/*require_once dirname(__FILE__).'/../../../../mouf/MoufManager.php';
if (!MoufManager::hasHiddenInstance()) {
	require_once dirname(__FILE__).'/../../../../Mouf.php';
}*/
use Mouf\MoufManager;

if (isset($_SERVER['BASE'])) {
	define('ROOT_URL', $_SERVER['BASE']."/");
} else {
	define('ROOT_URL', "/");
}

//require_once __DIR__.'/../../../autoload.php';
require_once __DIR__.'/../../../../mouf/Mouf.php';


$splash = MoufManager::getMoufManager()->getInstance('splash');

if (!isset($splashUrlPrefix)) {
	$splashUrlPrefix = ROOT_URL;
}

$splash->route($splashUrlPrefix);

?>