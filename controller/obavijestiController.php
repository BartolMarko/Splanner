<?php

class ObavijestiController extends BaseController
{
	public function index() 
	{
		$ss = new SplannerService();
		$povezaniKorisnici = $ss->dohvatiPovezaneKorisnike($_SESSION['id_user']);
		$this->registry->template->povezaniKorisnici = $povezaniKorisnici;

		$imenaKorisnika = $ss->getImenaKorisnika($povezaniKorisnici);
		$this->registry->template->imenaKorisnika = $imenaKorisnika;


		$obavijestiList = $ss->getAllObavijesti();

		$this->registry->template->title = 'Obavijesti';
		$this->registry->template->obavijestiList = $obavijestiList;

        $this->registry->template->show( 'obavijesti_index' );
	}

	public function filter()
    {
        if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
            http_response_code(400);
            echo 'Nevažeći podaci.';
            return;
        }

        $ids = array_map('intval', $_POST['ids']);
        $ss = new SplannerService();
        $grupe = $ss->dohvatiGrupeZaKorisnike($ids);
        if (empty($grupe)) {
            echo '<p>Nema obavijesti za odabrane korisnike.</p>';
            return;
        }
        $obavijestiList = $ss->getObavijestiZaGrupe($grupe);
        require __SITE_PATH . '/view/obavijesti_filter.php';
    }
}

?>
