<?php

class RasporedController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Raspored';
		$this->registry->template->cssFile = "raspored_style.css";

		$proba = SplannerService::getTerminiForUser(
			$_SESSION["id_user"],
			'2025-07-05',
			'2025-07-18'
		);

        $this->registry->template->show( 'raspored_index' );
	}

	public function termini() {
		$datumOd = isset($_GET['datumOd']) ? $_GET['datumOd'] : null;
		$datumDo = isset($_GET['datumDo']) ? $_GET['datumDo'] : null;

		if ($datumOd && $datumDo && isset($_SESSION["id_user"])) {
			$result = SplannerService::getTerminiForUser(
				$_SESSION["id_user"],
				$datumOd,
				$datumDo
			);
			header('Content-Type: application/json');
			echo json_encode($result);
			exit(0);
		}
		else {
			http_response_code(400);
			echo json_encode(['error' => 'Missing parameters']);
		}
	}
}

?>