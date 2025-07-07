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

        $id_grupe = $_GET['id'];

        $ss = new SplannerService();
        
		$this->registry->template->title = $ss->getAktivnostImeByIdGrupa($id_grupe);
		$this->registry->template->subtitle = $ss->getGrupaImeById($id_grupe);
		$this->registry->template->id_grupe = $id_grupe;
		
		// $commentList = $es->getAllComments($id_product);
		// $hasComments = !empty($commentList);
		// $this->registry->template->hasComments = $hasComments;
		// $this->registry->template->commentList = $commentList;

		// $this->registry->template->canRate = $es->userBoughtButDidNotRate($id_product, $_SESSION['id_user']);

		// $this->registry->template->canBuy = $es->canUserBuyProduct($id_product, $_SESSION['id_user']);

        $this->registry->template->show( 'aktivnosti_grupa' );
    }
}

?>