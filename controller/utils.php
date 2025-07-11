<?php



function dodajObavijestPosaljiMailove($id_grupe, $comment) {
    $ss = new SplannerService();
    $ss->dodajObavijest($id_grupe, $comment);

    $lista_mailova_korisnika = $ss->dohvatiEmailoveZaGrupu($id_grupe);

    require_once __DIR__ . '/../app/MailService.php';

    
    $subject = '=?UTF-8?B?' . base64_encode('Nova obavijest iz vaše grupe') . '?=';
    $textMessage = "Poštovani,\nImate novu obavijest u svojoj grupi. Molimo provjerite Splanner aplikaciju.";
    $htmlMessage = '
        <p>Poštovani,</p>
        <p>Imate novu obavijest u svojoj grupi. Molimo provjerite <a href="https://rp2.studenti.math.hr' . $_SERVER['PHP_SELF'] . '">Splanner</a>.</p>
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

?>