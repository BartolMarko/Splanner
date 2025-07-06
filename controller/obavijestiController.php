<?php

class ObavijestiController extends BaseController
{
	public function index() 
	{
		$ss = new SplannerService();
		$obavijestiList = $ss->getAllObavijesti();

		$this->registry->template->title = 'Obavijesti';
		$this->registry->template->obavijestiList = $obavijestiList;

        $this->registry->template->show( 'obavijesti_index' );
	}
}

?>