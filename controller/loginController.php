<?php 

class LoginController extends BaseController
{
    public function index()
    {   
        $this->registry->template->title = 'Prijava';


        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
    
            $ss = new SplannerService(); 

            if ($ss->checkLogin($username, $password)) {
                $_SESSION['username'] = $username;
                $_SESSION['id_user'] = $ss->getUserIdByName($_SESSION['username']);
    
                header('Location: ' . __SITE_URL . '/index.php?rt=raspored');
                exit();
            } 
            else {
                $this->registry->template->error = 'Krivo korisničko ime ili lozinka.';
                $this->registry->template->show('login_index');
            }
        } 
        else {
            $this->registry->template->show('login_index');
        }
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
