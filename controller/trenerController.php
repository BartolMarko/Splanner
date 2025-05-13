<?php

class TrenerController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Trener';

        $this->registry->template->show( 'trener_index' );
	}
}

?>