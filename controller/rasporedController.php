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

	public function userinfo() {
		if (!isset($_SESSION["id_user"]) || !isset($_SESSION["username"]) || !isset($_SESSION["tip_korisnika"])) {
			http_response_code(403);
			echo json_encode(['error' => 'Unauthorized']);
			exit(0);
		}
		$djeca = SplannerService::getDjecaKorisnika($_SESSION["id_user"]);
		$userInfo = [
			'id_korisnici' => $_SESSION["id_user"],
			'username' => $_SESSION["username"],
			'tip_korisnika' => $_SESSION["tip_korisnika"],
			'djeca' => $djeca
		];
		header('Content-Type: application/json');
		echo json_encode($userInfo);
		exit(0);
	}

	public function termini() {
		$datumOd = isset($_GET['datumOd']) ? $_GET['datumOd'] : null;
		$datumDo = isset($_GET['datumDo']) ? $_GET['datumDo'] : null;

		if (!$datumOd || !$datumDo || !isset($_SESSION["id_user"])) {
			http_response_code(400);
			echo json_encode(['error' => 'Missing parameters']);
			exit(0);
		}
		$activitiesById = [];
		$activitiesById[$_SESSION["id_user"]] = SplannerService::getTerminiForUser(
			$_SESSION["id_user"],
			$datumOd,
			$datumDo
		);
		if ($_SESSION["tip_korisnika"] == 'roditelj') {
			$djeca = SplannerService::getDjecaKorisnika($_SESSION["id_user"]);
			foreach ($djeca as $dijete) {
				$activitiesById[$dijete['id_korisnici']] = SplannerService::getTerminiForUser(
					$dijete['id_korisnici'],
					$datumOd,
					$datumDo
				);
			}
		}

		header('Content-Type: application/json');
		echo json_encode($activitiesById);
		exit(0);
	}
}

?>