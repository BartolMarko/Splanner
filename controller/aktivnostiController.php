<?php

class AktivnostiController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Aktivnosti';

        $this->registry->template->show( 'aktivnosti_index' );
	}
}

?>