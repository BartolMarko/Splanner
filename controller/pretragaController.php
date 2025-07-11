<?php

class PretragaController extends BaseController
{
	public function index() 
	{
		$this->registry->template->cssFile = "pretraga_style.css";
		$this->registry->template->title = 'Pretraga aktivnosti';

		$service = new SplannerService();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// Nova pretraga
			$ime = trim($_POST['ime'] ?? '');
			$grad = trim($_POST['grad'] ?? '');
			$spol = trim($_POST['spol'] ?? 'mješovito');
			$mojUzrast = is_numeric($_POST['uzrast']) ? (int)$_POST['uzrast'] : null;

			// Spremi kriterije u sesiju
			$_SESSION['zadnja_pretraga'] = [
				'ime' => $ime,
				'grad' => $grad,
				'spol' => $spol,
				'uzrast' => $mojUzrast
			];

			$rezultati = $service->searchGrupe($ime, $grad, $spol, $mojUzrast);

			$this->registry->template->rezultati = $rezultati;
			$this->registry->template->kriteriji = $_SESSION['zadnja_pretraga'];
			$this->registry->template->show('pretraga_rezultati');

		} elseif (isset($_GET['from']) && $_GET['from'] === 'pretraga' && isset($_SESSION['zadnja_pretraga'])) {
			// Ako je Natrag iz detalja
			$k = $_SESSION['zadnja_pretraga'];

			$rezultati = $service->searchGrupe(
				$k['ime'],
				$k['grad'],
				$k['spol'],
				$k['uzrast']
			);

			$this->registry->template->rezultati = $rezultati;
			$this->registry->template->kriteriji = $k;
			$this->registry->template->show('pretraga_rezultati');

		} else {
			// Prazna forma
			unset($_SESSION['zadnja_pretraga']);
			$this->registry->template->show('pretraga_index');
		}
	}



}

?>