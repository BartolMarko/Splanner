<?php

class AktivnostiController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Moje aktivnosti';

		if (!isset($_SESSION['username'])) { //nisi ulobiran, baca te na login
			header('Location: index.php?rt=login');
			exit();
		}

		$service = new SplannerService();

		if ($_SESSION['tip_korisnika'] === 'trener') {
			$aktivnosti = $service->getAktivnostiByTrainer($_SESSION['id_user']);
			$this->registry->template->aktivnosti = $aktivnosti;
			$this->registry->template->tip = 'trener';
		}
		else if ($_SESSION['tip_korisnika'] === 'roditelj') {
			$aktivnosti = $service->getAktivnostiForUser($_SESSION['id_user']);

			$djeca = $service->getDjecaByRoditelj($_SESSION['id_user']);

			$this->registry->template->aktivnosti = $aktivnosti;
			$this->registry->template->djeca = $djeca;
			$this->registry->template->tip = 'roditelj';
		}
		else { // dijete
			$aktivnosti = $service->getAktivnostiForUser($_SESSION['id_user']);
			$this->registry->template->aktivnosti = $aktivnosti;
			$this->registry->template->tip = 'dijete';
		}
			$this->registry->template->show( 'aktivnosti_index' );
	}
}

?>
