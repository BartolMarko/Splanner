<?php

class PostavkeController extends BaseController
{
    public function index()
    {
        $this->registry->template->title = 'Postavke';
        
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
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        if (preg_match('/\s/', $novoUsername)) {
            $this->registry->template->poruka = 'Korisničko ime ne smije sadržavati razmake!';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss = new SplannerService();

        if ($ss->checkIfUsernameExists($novoUsername)) {
            $this->registry->template->poruka = 'Korisničko ime je već zauzeto!';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss->updateUsername($_SESSION['id_user'], $novoUsername);
        $_SESSION['username'] = $novoUsername;

        $this->registry->template->poruka = 'Korisničko ime je uspješno promijenjeno.';
        $this->registry->template->tip_poruke = 'uspjeh';
        $this->index();
    }

    public function promjenaLozinke()
    {
        if (!isset($_SESSION['id_user'])) {
            header('Location: ' . __SITE_URL . '/index.php?rt=login');
            exit();
        }

        $stara = $_POST['stara_lozinka'];
        $nova = $_POST['nova_lozinka'];
        $nova2 = $_POST['nova_lozinka2'];

        if ($nova !== $nova2) {
            $this->registry->template->poruka = 'Nova lozinka i potvrda lozinke nisu iste.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        if (strlen($nova) < 6) {
            $this->registry->template->poruka = 'Nova lozinka mora imati barem 6 znakova.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        if ($stara === $nova) {
            $this->registry->template->poruka = 'Nova lozinka mora biti različita od stare.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss = new SplannerService();

        if (!$ss->provjeriLozinku($_SESSION['id_user'], $stara)) {
            $this->registry->template->poruka = 'Stara lozinka nije ispravna.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss->promijeniLozinku($_SESSION['id_user'], $nova);

        $this->registry->template->poruka = 'Lozinka je uspješno promijenjena.';
        $this->registry->template->tip_poruke = 'uspjeh';
        $this->index();
    }

    public function obrisiRacun()
    {
        if (!isset($_SESSION['id_user'])) {
            header('Location: ' . __SITE_URL . '/index.php?rt=login');
            exit();
        }

        $ss = new SplannerService();
        $ss->obrisiKorisnika($_SESSION['id_user']);

        session_unset();
        session_destroy();

        header('Location: ' . __SITE_URL . '/index.php?rt=login&msg=obrisan');
        exit();
    }

    public function dodajDijete()
    {
        if (!isset($_SESSION['id_user']) || $_SESSION['tip_korisnika'] !== 'roditelj') {
            header('Location: ' . __SITE_URL . '/index.php?rt=login');
            exit();
        }

        $username = trim($_POST['username']);
        $oib = trim($_POST['oib']);
        $password = $_POST['password'];

        if ($username === '' || $oib === '' || $password === '') {
            $this->registry->template->poruka = 'Sva polja su obavezna.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss = new SplannerService();

        if ($ss->checkIfUsernameExists($username)) {
            $this->registry->template->poruka = 'Korisničko ime je zauzeto.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $email = $ss->dohvatiEmailKorisnika($_SESSION['id_user']);

        $ss->dodajDijete(
            $_SESSION['id_user'],
            $username,
            $oib,
            $email,
            $password
        );

        $this->registry->template->poruka = 'Uspješno ste dodali novog člana obitelji.';
        $this->registry->template->tip_poruke = 'uspjeh';
        $this->index();
    }
}
?>
