<?php
namespace CM\Neos\ThemeModule\Controller;

use Neos\Flow\Annotations as Flow;
use \Neos\Flow\Mvc\Controller\ActionController;

class BackendController extends ActionController {
	public function indexAction() {
		$this->view->assign('exampleValue', 'Hello World');
	}
}
