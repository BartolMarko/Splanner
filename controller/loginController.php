<?php 

class LoginController extends BaseController
{
    public function index()
    {   
        $this->registry->template->title = 'Prijava';
        if (isset($_SESSION['username'])){
            header('Location: ' . __SITE_URL . '/index.php?rt=raspored');
        }
        else if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $ss = new SplannerService();
            $userRow = $ss->checkLogin($username, $password); // <---- umjesto zastavice samo -> cijeli row

            if (is_array($userRow)) {
                // Login uspjesan
                $_SESSION['username'] = $userRow['username'];
                $_SESSION['id_user'] = $userRow['id_korisnici'];
                $_SESSION['tip_korisnika'] = $userRow['tip_korisnika'];

                header('Location: ' . __SITE_URL . '/index.php?rt=raspored');
                exit();
            }
            else if ($userRow === 0) {
                $this->registry->template->error = 'Krivo korisničko ime ili lozinka.';
                $this->registry->template->show('login_index');
            }
            else if ($userRow === 2) {
                $this->registry->template->error = 'Niste dovršili registraciju, provjerite Vaš mail.';
                $this->registry->template->show('login_index');
            }
            else {
                // fallback za svaki slucaj
                $this->registry->template->error = 'Došlo je do neočekivane greške.';
                $this->registry->template->show('login_index');
            }
        } 
        else {
            $this->registry->template->show('login_index');
        }
    }


    public function registracija()
    {   
        $this->registry->template->title = 'Registracija';
        $ss = new SplannerService();

        if( !isset( $_POST['username'] ) || !isset( $_POST['password'] ) || !isset( $_POST['password_again'] ) || !isset( $_POST['email']) || !isset( $_POST['oib']) ){
            $this->registry->template->show('login_registracija');
        }
        else if(strlen($_POST['password']) < 5){
            $this->registry->template->error = 'Lozinka mora imati barem 5 znakova.';
            $this->registry->template->show('login_registracija');
        }
        else if($_POST['password'] !== $_POST['password_again']){
            $this->registry->template->error = 'Naveli ste dvije različite lozinke.';
            $this->registry->template->show('login_registracija');
        }
        else if( !filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL) ){
            $this->registry->template->error = 'Neispravna email adresa.';
            $this->registry->template->show('login_registracija');
        }
        else if($ss->checkIfUsernameOccupied($_POST['username'])){
            $this->registry->template->error = 'Ovo korisničko ime već postoji, molimo izaberite novo.';
            $this->registry->template->show('login_registracija');
        }
        else{
            while(1){
                $reg_seq = '';
                for( $i = 0; $i < 20; ++$i )
                    $reg_seq .= chr( rand(0, 25) + ord( 'a' ) );

                if($ss->checkRegSeq($reg_seq))
                    break;
            }

            $ss->addNewUser($_POST['username'], $_POST['password'], $_POST['email'], $_POST['oib'], $_POST['uloga'], $_POST['spol'], $_POST['datum'], $reg_seq);
            
            // slanje mail-a
            $to       = $_POST['email'];
            $subject  = 'Registracijski mail';
            $message  = 'Poštovani ' . $_POST['username'] . "!\nZa dovršetak registracije kliknite na sljedeći link: ";
            $message .= 'https://' . 'rp2.studenti.math.hr' . __SITE_URL . '/index.php?rt=login/potvrda&reg_seq=' . $reg_seq . "\n";
            $headers  = 'From: rp2@studenti.math.hr' . "\r\n" .
                        'Reply-To: rp2@studenti.math.hr' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

            $isOK = mail($to, $subject, $message, $headers);

            if( !$isOK )
                exit( 'Greška: ne mogu poslati mail. (Pokrenite na rp2 serveru.)' );

            $this->registry->template->show('login_slanje');
        }
    }

    public function slanje()
    {
        $this->registry->template->title = 'Registracija, posljednji korak';
        $this->registry->template->show('login_slanje');
    }

    public function potvrda()
    {
        $this->registry->template->title = 'Uspješna registracija';
        $ss = new SplannerService();

        $reg_seq = $_GET['reg_seq'];
        $ss->updateRegSeq($reg_seq);

        $this->registry->template->show('login_potvrda');
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: ' . __SITE_URL . '/index.php?rt=login');
        exit();
    }
}


?>