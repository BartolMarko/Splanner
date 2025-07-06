<?php

class RasporedController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Raspored';
		$this->registry->template->cssFile = "raspored_style.css";

        $this->registry->template->show( 'raspored_index' );
	}
}

?>