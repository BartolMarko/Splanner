<?php

class PretragaController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Pretraga aktivnosti';
		if(isset($_POST['sto_trazim'])){
			//prikazuju se rezultati pretrage
			$service = new SplannerService();
			$rezultati = $service->getAktByName(trim($_POST['sto_trazim']));
			$this->registry->template->rezultati = $rezultati;
			$this->registry->template->show('pretraga_rezultati');
		}
		else{ //ako nije pretraga, samo nacrtam stranicu za pretragu
            $this->registry->template->show( 'pretraga_index' );

		}
	}
}

?>