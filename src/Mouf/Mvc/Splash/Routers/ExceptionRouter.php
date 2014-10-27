<?php
namespace Mouf\Mvc\Splash\Routers;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Mouf\Mvc\Splash\Controllers\Http500HandlerInterface;
use Symfony\Component\BrowserKit\Response;

class ExceptionRouter implements HttpKernelInterface {
	
	/**
	 * The logger used by Splash
	 *
	 * Note: accepts both old and new PSR-3 compatible logger
	 *
	 * @var LoggerInterface
	 */
	private $log;
	
	/**
	 * @var HttpKernelInterface
	 */
	private $router;
	
	/**
	 * The controller that will display 500 errors
	 * @var Http500HandlerInterface
	 */
	private $errorController;
	

	/**
	 * The "500" message
	 * @var string|ValueInterface
	 */
	private $message = "Page not found";
	
	public function __construct(HttpKernelInterface $router, LoggerInterface $log = null){
		$this->router = $router;
		$this->log = $log;
	}
	
	/**
	 * Handles a Request to convert it to a Response.
	 *
	 * When $catch is true, the implementation must catch all exceptions
	 * and do its best to convert them to a Response instance.
	 *
	 * @param Request $request A Request instance
	 * @param int     $type    The type of the request
	 *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
	 * @param bool    $catch Whether to catch exceptions or not
	 *
	 * @return Response A Response instance
	 *
	 * @throws \Exception When an Exception occurs during processing
	 */
	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true){
		if ($catch){
			try {
				return $this->router->handle($request, $type, false);
			} catch (\Exception $e) {
				$this->handleException($e);
			}		
		}else{
			return $this->router->handle($request, $type);
		}
	}
	
	private function handleException(\Exception $e) {
		if ($this->log != null) {
			if ($this->log instanceof LogInterface) {
				$this->log->error($e);
			} else {
				$this->log->error("Exception thrown inside a controller.", array(
						'exception' => $e
				));
			}
		} else {
			// If no logger is set, let's log in PHP error_log
			error_log($e->getMessage()." - ".$e->getTraceAsString());
		}
	
		$debug = $this->debugMode;
		
		ob_start();
		$this->errorController->serverError($e);
		$html = ob_get_clean();
		return new Response($html, 500);
		
	}
	
	/**
	 * The "404" message
	 * @param string|ValueInterface $message
	 */
	public function setMessage($message){
		$this->message = $message;
	}
	
}