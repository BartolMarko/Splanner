<?php

require_once __DIR__ . '/utils.php';

class AktivnostiController extends BaseController
{
	public function index() 
	{
		$this->registry->template->title = 'Aktivnosti';

        $this->registry->template->show( 'aktivnosti_index' );
	}

	function zadovoljava($user, $grupa_detalji) {
		if ($user['spol'] !== $grupa_detalji['spol'] && $grupa_detalji['spol'] !== 'mješovito')
			return false;
	
		if (empty($user['datum_rodenja']))
			return false;
	
		$datum_rodjenja = new DateTime($user['datum_rodenja']);
		$danas = new DateTime();
	
		$razlika = $danas->diff($datum_rodjenja);
		$godine = $razlika->y;
	
		if ($grupa_detalji['uzrast_od'] !== NULL && $godine < $grupa_detalji['uzrast_od'])
			return false;
	
		if ($grupa_detalji['uzrast_do'] !== NULL && $godine > $grupa_detalji['uzrast_do'])
			return false;
	
		return true;
	}
	

	public function grupa() {

        if (!isset($_GET['id'])) {
            echo 'Greška: nije moguće dohvatiti grupu.';
            return;
        }

		$id_grupe = $_GET['id'];
		$ss = new SplannerService();

		// BRISANJE OBAVIJESTI
		if (isset($_POST['id_obavijesti'])) {
			$id_obavijesti = intval($_POST['id_obavijesti']);
	
			$ss->obrisiObavijest($id_obavijesti);
		}
	
		// DODAVANJE NOVE OBAVIJESTI
		if(isset($_POST['id_grupe']) && isset($_POST['comment'])){
			$id_grupe = intval($_POST['id_grupe']);
			$comment = trim($_POST['comment']);

			dodajObavijestPosaljiMailove($id_grupe, $comment);
		}

		//UPIS ČLANA
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_clana_upis'])) {
			$id_clana = $_POST['id_clana_upis'];
			$ss->dodajKorisnikaUGrupu((int)$id_clana, (int)$id_grupe);
			header('Location: ' . __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $id_grupe);
			exit();
		}

		
        //ISPIS ČLANA
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_clana_ispis'])) {
			$id_clana = $_POST['id_clana_ispis'];
			$ss->obrisiKorisnikaIzGrupe((int)$id_clana, (int)$id_grupe);
			header('Location: ' . __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $id_grupe);
			exit();
		}

		$aktivnost_detalji = $ss->getAktivnostByIdGrupa($id_grupe);
		$grupa_detalji = $ss->getGrupaById($id_grupe);
        
		$this->registry->template->title = $grupa_detalji['ime'];
		$this->registry->template->naziv_aktivnosti = $aktivnost_detalji['ime'];
		$this->registry->template->grupa_detalji = $grupa_detalji;
		$this->registry->template->aktivnost_detalji = $aktivnost_detalji;

		$this->registry->template->obavijestiList = $ss->getObavijestiZaGrupuFromId($id_grupe);
		$this->registry->template->ime_trenera = $ss->getImePrezimeKorisnikaFormId($aktivnost_detalji['fk_id_trenera']);

		$povezaniKorisnici = $ss->dohvatiPovezaneKorisnike($_SESSION['id_user']);
		$this->registry->template->povezaniKorisnici = $povezaniKorisnici;

		$povezaniUGrupi = $ss->getClanoviGrupeIzListeKorisnika($id_grupe, $povezaniKorisnici);

		$imenaPovezanihUGrupi = $ss->getImenaKorisnika($povezaniUGrupi);
		$this->registry->template->imenaPovezanihUGrupi = $imenaPovezanihUGrupi;

		$povezaniZaUpis = array_diff($povezaniKorisnici, $povezaniUGrupi); //koje potencijalno mozemo upisati
		$this->registry->template->povezaniZaUpis = $povezaniZaUpis;
		
		$clanoviKojeMozesUpisati = [];

		foreach ($povezaniZaUpis as $id) {
			$user = $ss->getKorisnikaFromId($id);
		
			if ($this->zadovoljava($user, $grupa_detalji))
				$clanoviKojeMozesUpisati[] = $id;
		}

		$this->registry->template->clanoviZaUpisId = $clanoviKojeMozesUpisati;
		$clanoviZaUpis = $ss->getImenaKorisnika($clanoviKojeMozesUpisati);
		$this->registry->template->clanoviZaUpis = $clanoviZaUpis;
		//$clanoviZaUpis su svi tvoji povezani korisnici koji još nisu u grupi i zadovoljavaju uvjete.

		//ČLANOVI GRUPE ZA TRENERA
		$clanoviGrupe = $ss->dohvatiIdeveClanovaGrupe($id_grupe);
		$this->registry->template->imenaClanovaGrupe = $ss->getImenaKorisnika($clanoviGrupe, true);

        $this->registry->template->show( 'aktivnosti_grupa' );
    }

}

?>