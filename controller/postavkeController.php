<?php

class PostavkeController extends BaseController
{
    public function index()
    {
        $this->registry->template->title = 'Postavke';

        if (!isset($_SESSION['id_user'])) {
            header('Location: ' . __SITE_URL . '/index.php?rt=login');
            exit();
        }

        $ss = new SplannerService();
        $lista_djece = [];

        // Ako je roditelj, dohvaća djecu i status obavijesti
        if ($_SESSION['tip_korisnika'] === 'roditelj') {
            $lista_djece = $ss->dohvatiDjecu($_SESSION['id_user']);
            $this->registry->template->prima_obavijesti = $ss->dohvatiPrimaObavijesti($_SESSION['id_user']);
            $this->registry->template->lista_djece = $lista_djece;

            $djecaGrupe = [];
            foreach ($lista_djece as $dijete) {
                $djecaGrupe[$dijete['username']] = $ss->dohvatiGrupeZaKorisnika($dijete['id_korisnici']);
            }
            $this->registry->template->djecaGrupe = $djecaGrupe;
        } else {
            // Ako nije roditelj
            $this->registry->template->djecaGrupe = [];
            $this->registry->template->prima_obavijesti = null;
            $this->registry->template->lista_djece = [];
        }

        // Ako je trener
        if ($_SESSION['tip_korisnika'] === 'trener') {
            $zaradaGrupe = $ss->dohvatiZaraduPoGrupamaTrenera($_SESSION['id_user']);

            $ukupno = 0;
            foreach ($zaradaGrupe as $g) {
                if ($g['broj_polaznika'] > 0) {
                    $ukupno += $g['cijena'] * $g['broj_polaznika'];
                }
            }

            $this->registry->template->zaradaGrupe = $zaradaGrupe;
            $this->registry->template->ukupna_zarada = $ukupno;
        }
      
    $this->registry->template->mojeGrupe = $ss->dohvatiGrupeZaKorisnika($_SESSION['id_user']);

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

        // Spremi stari username
        $stariUsername = $_SESSION['username'];

        // Promijeni username
        $ss->updateUsername($_SESSION['id_user'], $novoUsername);
        $_SESSION['username'] = $novoUsername;

        // Ako je dijete, šalji roditelju obavijest
        if ($_SESSION['tip_korisnika'] === 'dijete') {
            // Dohvati roditelja
            $roditelj = $ss->dohvatiRoditeljaOdDjeteta($_SESSION['id_user']);

            if ($roditelj && $roditelj['prima_obavijest']) {
                $emailRoditelja = $roditelj['email'];

                require_once __DIR__ . '/../app/MailService.php';

                $subject = '=?UTF-8?B?' . base64_encode('Obavijest o promjeni korisničkog imena') . '?=';
                $textMessage = "Poštovani {$roditelj['username']},\n\nVaše dijete je promijenilo svoje korisničko ime.\n\nStaro korisničko ime: {$stariUsername}\nNovo korisničko ime: {$novoUsername}";
                $htmlMessage = "
                    <p>Poštovani {$roditelj['username']},</p>
                    <p>Vaše dijete je promijenilo svoje korisničko ime.</p>
                    <p>Staro korisničko ime: <strong>{$stariUsername}</strong></p>
                    <p>Novo korisničko ime: <strong>{$novoUsername}</strong></p>
                    <p>Lijep pozdrav,<br>Splanner</p>
                ";
                $htmlMessage = mb_convert_encoding($htmlMessage, 'UTF-8', 'auto'); //moza ne treba??
                try {
                    MailService::posaljiMail($emailRoditelja, $subject, $textMessage, $htmlMessage);
                } catch (Exception $e) {
                    // Ne prekidaj zbog greške kod slanja maila
                }
            }
        }

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

        $username = trim($_POST['username'] ?? '');
        $ime = trim($_POST['ime'] ?? '');
        $prezime = trim($_POST['prezime'] ?? '');
        $oib = trim($_POST['oib']);
        $password = $_POST['password'];
        $password_again = $_POST['password_again'] ?? '';
        $spol = $_POST['spol'] ?? '';
        $datum = $_POST['datum'] ?? '';

        // Validacija polja
        if ($username === '' || $oib === '' || $password === '' || $password_again === '' || $spol === '' || $datum === '' ||
            $ime === '' || $prezime === '') {
            $this->registry->template->poruka = 'Sva polja su obavezna.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        if (strlen($password) < 6) {
            $this->registry->template->poruka = 'Lozinka mora imati barem 6 znakova.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        if ($password !== $password_again) {
            $this->registry->template->poruka = 'Lozinke se ne podudaraju.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss = new SplannerService();

        if ($ss->checkIfUsernameExists($username)) {
            $this->registry->template->poruka = 'Korisničko ime je već zauzeto.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        // Za dijete ćemo uzeti email roditelja
        $email = $ss->dohvatiEmailKorisnika($_SESSION['id_user']);

        // Spremi dijete u bazu
        $ss->dodajDijete(
            $_SESSION['id_user'],
            $username,
            $ime,
            $prezime,
            $oib,
            $email,
            $password,
            $spol,
            $datum
        );

        $this->registry->template->poruka = 'Uspješno ste dodali novog člana obitelji.';
        $this->registry->template->tip_poruke = 'uspjeh';
        $this->index();
    }

    public function obrisiDijete()
    {
        if (!isset($_SESSION['id_user']) || $_SESSION['tip_korisnika'] !== 'roditelj') {
            header('Location: ' . __SITE_URL . '/index.php?rt=login');
            exit();
        }

        if (!isset($_POST['id_djeteta'])) {
            $this->registry->template->poruka = 'Niste odabrali dijete za brisanje.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $id_djeteta = intval($_POST['id_djeteta']);
        $ss = new SplannerService();

        if (!$ss->provjeriDijeteId($_SESSION['id_user'], $id_djeteta)) {
            $this->registry->template->poruka = 'Ovaj korisnik nije vaše dijete.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss->obrisiDijeteId($_SESSION['id_user'], $id_djeteta);

        $this->registry->template->poruka = 'Dijete je uspješno obrisano.';
        $this->registry->template->tip_poruke = 'uspjeh';
        $this->index();
    }

    public function promijeniObavijesti()
    {
        if (!isset($_SESSION['id_user'])) {
            header('Location: ' . __SITE_URL . '/index.php?rt=login');
            exit();
        }

        $prima = isset($_POST['prima_obavijesti']) ? 1 : 0;

        $ss = new SplannerService();
        $ss->postaviPrimaObavijesti($_SESSION['id_user'], $prima);

        $this->registry->template->poruka = 'Postavke za obavijesti su spremljene.';
        $this->registry->template->tip_poruke = 'uspjeh';
        $this->index();
    }

    public function promjenaEmaila()
    {
        if (!isset($_SESSION['id_user'])) {
            header('Location: ' . __SITE_URL . '/index.php?rt=login');
            exit();
        }

        $noviEmail = trim($_POST['novi_email']);

        if ($noviEmail === '') {
            $this->registry->template->poruka = 'Email ne smije biti prazan.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        if (!filter_var($noviEmail, FILTER_VALIDATE_EMAIL)) {
            $this->registry->template->poruka = 'Unesite ispravnu email adresu.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss = new SplannerService();

         if ($ss->emailExists($noviEmail)) {
            $this->registry->template->poruka = 'Taj email je već u upotrebi.';
            $this->registry->template->tip_poruke = 'greska';
            $this->index();
            return;
        }

        $ss->promijeniEmail($_SESSION['id_user'], $noviEmail);

        $this->registry->template->poruka = 'Email je uspješno promijenjen.';
        $this->registry->template->tip_poruke = 'uspjeh';
        $this->index();
    }




}
?>
