<?php

class ObavijestiController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Obavijesti';

        $this->registry->template->show( 'obavijesti_index' );
	}
}

?>