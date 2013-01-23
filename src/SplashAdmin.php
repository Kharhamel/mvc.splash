<?php

use Mouf\MoufUtils;

use Mouf\MoufManager;

$moufManager = MoufManager::getMoufManager();

$moufManager->declareComponent('splashGenerateService', 'Mouf\\Mvc\\Splash\\SplashGenerateService', true);

$moufManager->declareComponent('splashApacheConfig', 'Mouf\\Mvc\\Splash\\Controllers\\Admin\\SplashAdminApacheConfigureController', true);
$moufManager->bindComponent('splashApacheConfig', 'template', 'moufTemplate');
$moufManager->bindComponents('splashApacheConfig', 'content', 'block.content');
$moufManager->bindComponent('splashApacheConfig', 'splashGenerateService', 'splashGenerateService');

$moufManager->declareComponent('splashinstall', 'Mouf\\Mvc\\Splash\\Controllers\\Admin\\SplashInstallController', true);
$moufManager->bindComponent('splashinstall', 'template', 'moufInstallTemplate');
$moufManager->bindComponents('splashinstall', 'content', 'block.content');
$moufManager->bindComponent('splashinstall', 'splashGenerateService', 'splashGenerateService');

$moufManager->declareComponent('splashpurgecache', 'Mouf\\Mvc\\Splash\\Controllers\\Admin\\SplashPurgeCacheController', true);
$moufManager->bindComponent('splashpurgecache', 'template', 'moufTemplate');
$moufManager->bindComponents('splashpurgecache', 'content', 'block.content');

$moufManager->declareComponent('splashHtaccessValidator', 'Mouf\\Validator\\MoufBasicValidationProvider', true);
$moufManager->setParameter('splashHtaccessValidator', 'name', 'Splash validator');
$moufManager->setParameter('splashHtaccessValidator', 'url', MoufUtils::getUrlPathFromFilePath(__DIR__.'/direct/splash_htaccess_validator.php', true));
$moufManager->setParameter('splashHtaccessValidator', 'propagatedUrlParameters', array('selfedit'));
$moufManager->getInstance("validatorService")->validators[] = $moufManager->getInstance("splashHtaccessValidator");

MoufAdmin::getValidatorService()->registerBasicValidator('Splash validator', MoufUtils::getUrlPathFromFilePath(__DIR__.'/direct/splash_instance_validator.php', true));

MoufUtils::registerMainMenu('mvcMainMenu', 'MVC', null, 'mainMenu', 100);
MoufUtils::registerMenuItem('mvcSplashSubMenu', 'Splash MVC', null, 'mvcMainMenu', 45);
MoufUtils::registerMenuItem('mvcSplashPurgeCacheItem', 'Purge URLs cache', 'splashpurgecache/', 'mvcSplashSubMenu', 0);
MoufUtils::registerMenuItem('mvcSplashAdminApacheConfig2Item', 'Configure Apache redirection', 'splashApacheConfig/', 'mvcSplashSubMenu', 45);


?>