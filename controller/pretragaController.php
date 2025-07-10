<?php

class PretragaController extends BaseController
{
		public function index() 
	{
		$this->registry->template->cssFile = "pretraga_style.css";
		$this->registry->template->title = 'Pretraga aktivnosti';

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$ime = trim($_POST['ime'] ?? '');
				$grad = trim($_POST['grad'] ?? '');
				$spol = trim($_POST['spol'] ?? 'oboje');
				$uzod = is_numeric($_POST['uzrast_od']) ? (int)$_POST['uzrast_od'] : 0;
				$uzdo = is_numeric($_POST['uzrast_do']) ? (int)$_POST['uzrast_do'] : 99;

			$service = new SplannerService();
			$rezultati = $service->searchGrupe($ime,$grad,$spol,$uzod,$uzdo);
			$this->registry->template->rezultati = $rezultati;
			$this->registry->template->show('pretraga_rezultati');
		}
		else {
			$this->registry->template->show('pretraga_index');
		}
	}
}

?>