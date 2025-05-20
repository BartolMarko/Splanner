<?php

class PostavkeController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Postavke';

        $this->registry->template->show( 'postavke_index' );

	}
}

?>
