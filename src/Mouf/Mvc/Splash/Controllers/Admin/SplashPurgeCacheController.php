<?php 
namespace Mouf\Mvc\Splash\Controllers\Admin;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Mvc\Splash\Controllers\Controller;

/**
 * The controller that will purge the URLs cache.
 *
 * @Component
 */
class SplashPurgeCacheController extends Controller {

	/**
	 * The template used by the Splash page.
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
	 * Displays the config page. 
	 *
	 * @Action
	 */
	public function defaultAction($selfedit = 'false') {
		
		if ($selfedit == "true") {
			$moufManager = MoufManager::getMoufManager();
		} else {
			$moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		// TODO: call the proxy for this method
		MoufProxy::getInstance('splash', $selfedit == "true")->purgeUrlsCache();
		
		$this->content->addFile(__DIR__."/../../../../views/admin/purgedCache.php", $this);
		$this->template->toHtml();
	}
}

?>