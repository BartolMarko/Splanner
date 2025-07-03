<?php

class PostavkeController extends BaseController
{
	public function index() 
    {
        $this->registry->template->title = 'Postavke';
        $this->registry->template->poruka = '';
        $this->registry->template->show('postavke_index');
    }

   public function promjenaUsername()
	{
		if (!isset($_SESSION['id_user'])) {
			header('Location: ' . __SITE_URL . '/index.php?rt=login');
			exit();
		}

		$novoUsername = trim($_POST['novo_username']);

		if ($novoUsername === '') {
			$this->registry->template->poruka = 'Korisničko ime ne smije biti prazno!';
			$this->registry->template->show('postavke_index');
			return;
		}

		if (preg_match('/\s/', $novoUsername)) {
			$this->registry->template->poruka = 'Korisničko ime ne smije sadržavati razmake!';
			$this->registry->template->show('postavke_index');
			return;
		}

		$ss = new SplannerService();

		if ($ss->checkIfUsernameExists($novoUsername)) {
			$this->registry->template->poruka = 'Korisničko ime je već zauzeto!';
			$this->registry->template->show('postavke_index');
			return;
		}

		$ss->updateUsername($_SESSION['id_user'], $novoUsername);

		$_SESSION['username'] = $novoUsername;

		$this->registry->template->poruka = 'Korisničko ime je uspješno promijenjeno.';
		$this->registry->template->show('postavke_index');

	}

}

?>
