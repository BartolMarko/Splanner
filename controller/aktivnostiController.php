<?php

class AktivnostiController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Aktivnosti';

        $this->registry->template->show( 'aktivnosti_index' );
	}

	public function grupa() {

        if (!isset($_GET['id'])) {
            echo 'Greška: nije moguće dohvatiti grupu.';
            return;
        }

		// BRISANJE OBAVIJESTI
		if (isset($_POST['id_obavijesti'])) {
			$id_obavijesti = intval($_POST['id_obavijesti']);
	
			$ss = new SplannerService();
			$ss->obrisiObavijest($id_obavijesti);
		}
	
		// DODAVANJE NOVE OBAVIJESTI
		if(isset($_POST['id_grupe']) && isset($_POST['comment'])){
			$id_grupe = intval($_POST['id_grupe']);
			$comment = trim($_POST['comment']);

			$ss = new SplannerService();
			$ss->dodajObavijest($id_grupe, $comment);

			$lista_mailova_korisnika = $ss->dohvatiEmailoveZaGrupu($id_grupe);

			require_once __DIR__ . '/../app/MailService.php';

			
			$subject = '=?UTF-8?B?' . base64_encode('Nova obavijest iz vaše grupe') . '?=';
			$textMessage = "Poštovani,\nImate novu obavijest u svojoj grupi. Molimo provjerite Splanner aplikaciju.";
			$htmlMessage = '
				<p>Poštovani,</p>
				<p>Imate novu obavijest u svojoj grupi. Molimo provjerite <a href="https://rp2.studenti.math.hr' . __SITE_URL . '">Splanner</a>.</p>
				<h3>Obavijest</h3>
				<p>Grupa: '.$ss->getGrupaImeById($id_grupe).'</p>
				<p>'.$comment.'</p>
				<br>
				<p>Lijep pozdrav,<br>Splanner tim</p>
			';

			foreach ($lista_mailova_korisnika as $to) {
				try {
					$isOK = MailService::posaljiMail($to, $subject, $textMessage, $htmlMessage);
				} catch (Exception $e) {
					echo 'Greška kod slanja maila na adresu ' . $to . ': ' . $e->getMessage() . "<br />";
					continue;
				}

				if (!$isOK) {
					echo 'Neuspješno slanje na adresu: ' . $to . "<br />";
				}
			}

		}

        $id_grupe = $_GET['id'];

        $ss = new SplannerService();

		$aktivnost_detalji = $ss->getAktivnostByIdGrupa($id_grupe);
		$grupa_detalji = $ss->getGrupaById($id_grupe);
        
		$this->registry->template->title = $aktivnost_detalji['ime'];
		$this->registry->template->subtitle = $grupa_detalji['ime'];
		$this->registry->template->grupa_detalji = $grupa_detalji;
		$this->registry->template->aktivnost_detalji = $aktivnost_detalji;

		$this->registry->template->obavijestiList = $ss->getObavijestiZaGrupuFromId($id_grupe);
		$this->registry->template->ime_trenera = $ss->getImeKorisnikaFormId($aktivnost_detalji['fk_id_trenera']);

        $this->registry->template->show( 'aktivnosti_grupa' );
    }

}

?>