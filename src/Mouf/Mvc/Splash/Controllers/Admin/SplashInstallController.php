<?php
namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\Actions\InstallUtils;

use Mouf\MoufUtils;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\SplashGenerateService;
use Mouf\MoufManager;
use Mouf\Mvc\Splash\Controllers\Controller;


/**
 * The controller used in the Splash install process.
 * 
 * @Component
 */
class SplashInstallController extends Controller {
	
	public $selfedit;
	
	/**
	 * The active MoufManager to be edited/viewed
	 *
	 * @var MoufManager
	 */
	public $moufManager;

	/**
	 * The service in charge of generating files.
	 * 
	 * @Property
	 * @Compulsory
	 * @var SplashGenerateService
	 */
	public $splashGenerateService;
	
	
	/**
	 * The template used by the install page.
	 *
	 * @Property
	 * @Compulsory
	 * @var TemplateInterface
	 */
	public $template;
	
	/**
	 *
	 * @var HtmlBlock
	 */
	public $content;
	
	/**
	 * Displays the first install screen.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only) 
	 */
	public function defaultAction($selfedit = "false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
				
		$this->content->addFile(__DIR__."/../../../../../views/admin/installStep1.php", $this);
		$this->template->toHtml();
	}

	/**
	 * Skips the install process.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only)
	 */
	public function skip($selfedit = "false") {
		InstallUtils::continueInstall($selfedit == "true");
	}

	protected $sourceDirectory;
	protected $controllerNamespace;
	protected $viewDirectory;
	
	/**
	 * Displays the second install screen.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only) 
	 */
	public function configure($selfedit = "false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$autoloadNamespaces = MoufUtils::getAutoloadNamespaces();
		if ($autoloadNamespaces) {
			$rootNamespace = $autoloadNamespaces[0]['namespace'].'\\';
			$this->sourceDirectory = $autoloadNamespaces[0]['directory'];
		} else {
                        set_user_message('<strong>Warning</strong> : Mouf could not find a PSR 0 autoloader configured in your composer.json file. Therefore, unless you are using your own autoloader, it is likely that mouf will be unable to find the Splash Controllers. 
                            <br/>You should : <ol>
                            <li><a href="http://getcomposer.org/doc/04-schema.md#psr-0" target="_blank">Configure PSR 0 in your composer.json</a></li>
                            <li>Regenerate your composer autoloader : <pre>php composer.phar dumpautoload</pre></li>
                            <li>Refresh this page</li>
</ol>');
			$rootNamespace = '';
		}
		
		$this->controllerNamespace = $this->moufManager->getVariable("splashDefaultControllersNamespace");
		if ($this->controllerNamespace == null) {
			$this->controllerNamespace = $rootNamespace."Controllers";
		}
		$this->viewDirectory = $this->moufManager->getVariable("splashDefaultViewsDirectory");
		if ($this->viewDirectory == null) {
			$this->viewDirectory = "views/";
		}
		
		$this->content->addFile(__DIR__."/../../../../../views/admin/installStep2.php", $this);
		$this->template->toHtml();
	}
	
	/**
	 * This action generates the TDBM instance, then the DAOs and Beans. 
	 * 
	 * @Action
	 * @param string $sourcedirectory
	 * @param string $controllernamespace
	 * @param string $viewdirectory
	 * @param string $selfedit
	 */
	public function generate($sourcedirectory, $controllernamespace, $viewdirectory, $selfedit="false") {
		$this->selfedit = $selfedit;		
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$sourcedirectory = trim($sourcedirectory, "/\\");
		$sourcedirectory .= "/";
		$controllernamespace = trim($controllernamespace, "/\\");
		$controllernamespace .= "\\";
		$viewdirectory = trim($viewdirectory, "/\\");
		$viewdirectory .= "/";
		
		$this->moufManager->setVariable("splashDefaultSourceDirectory", $sourcedirectory);
		$this->moufManager->setVariable("splashDefaultControllersNamespace", $controllernamespace);
		$this->moufManager->setVariable("splashDefaultViewsDirectory", $viewdirectory);
		
		
		// Let's start by performing basic checks about the instances we assume to exist.
		if (!$this->moufManager->instanceExists("bootstrapTemplate")) {
			$this->displayErrorMsg("The install process assumes there is a template whose instance name is 'bootstrapTemplate'. Could not find the 'bootstrapTemplate' instance.");
			return;
		}
		
		
		// These instances are expected to exist when the installer is run.
		$bootstrapTemplate = $this->moufManager->getInstanceDescriptor('bootstrapTemplate');
		$block_content = $this->moufManager->getInstanceDescriptor('block.content');
		
		// Let's create the instances.
		$splash = InstallUtils::getOrCreateInstance('splash', 'Mouf\\Mvc\\Splash\\Splash', $this->moufManager);
		$exceptionRouter = InstallUtils::getOrCreateInstance('exceptionRouter', 'Mouf\\Mvc\\Splash\\Routers\\ExceptionRouter', $this->moufManager);
		$splashDefaultRouter = InstallUtils::getOrCreateInstance('splashDefaultRouter', 'Mouf\\Mvc\\Splash\\Routers\\SplashDefaultRouter', $this->moufManager);
		$notFoundRouter = InstallUtils::getOrCreateInstance('notFoundRouter', 'Mouf\\Mvc\\Splash\\Routers\\NotFoundRouter', $this->moufManager);
		$httpErrorsController = InstallUtils::getOrCreateInstance('httpErrorsController', 'Mouf\\Mvc\\Splash\\Controllers\\HttpErrorsController', $this->moufManager);
		$splashCacheApc = InstallUtils::getOrCreateInstance('splashCacheApc', 'Mouf\\Utils\\Cache\\ApcCache', $this->moufManager);
		$splashCacheFile = InstallUtils::getOrCreateInstance('splashCacheFile', 'Mouf\\Utils\\Cache\\FileCache', $this->moufManager);
		
		// Let's bind instances together.
		if (!$splash->getConstructorArgumentProperty('router')->isValueSet()) {
			$splash->getConstructorArgumentProperty('router')->setValue($exceptionRouter);
		}
		if (!$exceptionRouter->getConstructorArgumentProperty('router')->isValueSet()) {
			$exceptionRouter->getConstructorArgumentProperty('router')->setValue($splashDefaultRouter);
		}
		if (!$exceptionRouter->getConstructorArgumentProperty('errorController')->isValueSet()) {
			$exceptionRouter->getConstructorArgumentProperty('errorController')->setValue($httpErrorsController);
		}
		if (!$splashDefaultRouter->getConstructorArgumentProperty('fallBackRouter')->isValueSet()) {
			$splashDefaultRouter->getConstructorArgumentProperty('fallBackRouter')->setValue($notFoundRouter);
		}
		if (!$splashDefaultRouter->getConstructorArgumentProperty('cacheService')->isValueSet()) {
			$splashDefaultRouter->getConstructorArgumentProperty('cacheService')->setValue($splashCacheApc);
		}
		if (!$notFoundRouter->getConstructorArgumentProperty('pageNotFoundController')->isValueSet()) {
			$notFoundRouter->getConstructorArgumentProperty('pageNotFoundController')->setValue($httpErrorsController);
		}
		if (!$httpErrorsController->getPublicFieldProperty('template')->isValueSet()) {
			$httpErrorsController->getPublicFieldProperty('template')->setValue($bootstrapTemplate);
		}
		if (!$httpErrorsController->getPublicFieldProperty('contentBlock')->isValueSet()) {
			$httpErrorsController->getPublicFieldProperty('contentBlock')->setValue($block_content);
		}
		if (!$httpErrorsController->getPublicFieldProperty('debugMode')->isValueSet()) {
			$httpErrorsController->getPublicFieldProperty('debugMode')->setValue('DEBUG');
		$httpErrorsController->getPublicFieldProperty('debugMode')->setOrigin("config");
		}
		if (!$splashCacheApc->getPublicFieldProperty('prefix')->isValueSet()) {
			$splashCacheApc->getPublicFieldProperty('prefix')->setValue('SECRET');
		$splashCacheApc->getPublicFieldProperty('prefix')->setOrigin("config");
		}
		if (!$splashCacheApc->getPublicFieldProperty('fallback')->isValueSet()) {
			$splashCacheApc->getPublicFieldProperty('fallback')->setValue($splashCacheFile);
		}
		if (!$splashCacheFile->getPublicFieldProperty('prefix')->isValueSet()) {
			$splashCacheFile->getPublicFieldProperty('prefix')->setValue('SECRET');
		$splashCacheFile->getPublicFieldProperty('prefix')->setOrigin("config");
		}
		if (!$splashCacheFile->getPublicFieldProperty('cacheDirectory')->isValueSet()) {
			$splashCacheFile->getPublicFieldProperty('cacheDirectory')->setValue('splashCache/');
		}
		
		// Let's rewrite the MoufComponents.php file to save the component
		$this->moufManager->rewriteMouf();



		if (!$this->moufManager->instanceExists("rootController")) {
			$this->splashGenerateService->generateRootController($sourcedirectory, $controllernamespace, $viewdirectory);

			$this->moufManager->declareComponent("rootController", $controllernamespace."RootController");
			$this->moufManager->bindComponent("rootController", "template", "bootstrapTemplate");
			$this->moufManager->bindComponent("rootController", "content", "block.content");
				
			// TODO: bind au ErrorLogLogger?
		}
		
		
		$this->moufManager->rewriteMouf();
				
		InstallUtils::continueInstall($selfedit == "true");
	}
	
	/**
	 * Write .htaccess
	 *
	 * @Action
	 * @param string $selfedit
	 */
	public function writeHtAccess($selfedit="false") {
		/*$uri = $_SERVER["REQUEST_URI"];
		
		$installPos = strpos($uri, "/vendor/mouf/mouf/splashinstall/writeHtAccess");
		if ($installPos !== FALSE) {
			$uri = substr($uri, 0, $installPos);
		}
		if (empty($uri)) {
			$uri = "/";
		}*/
		
		if ($selfedit == "true") {
			$moufManager = MoufManager::getMoufManager();
		} else {
			$moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$this->exludeExtentions = $moufManager->getVariable("splashexludeextentions");
		$this->exludeFolders = $moufManager->getVariable("splashexludefolders");
		if (empty($this->exludeExtentions)){
			$this->exludeExtentions = array("js", "ico", "gif", "jpg", "png", "css", "woff", "ttf", "svg", "eot", "map");
		}
		if (empty($this->exludeFolders)){
			$this->exludeFolders = array("vendor");
		}
		
		$this->splashGenerateService->writeHtAccess($this->exludeExtentions, $this->exludeFolders);
		
		InstallUtils::continueInstall($selfedit == "true");
	}
	
	protected $errorMsg;
	
	private function displayErrorMsg($msg) {
		$this->errorMsg = $msg;
		$this->content->addFile(dirname(__FILE__)."/../../../../../views/admin/installError.php", $this);
		$this->template->toHtml();
	}
}