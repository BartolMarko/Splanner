<?php

require_once __DIR__ . '/../model/splannerservice.class.php';
require_once __DIR__ . '/../controller/utils.php';

session_start();

function sendJSONandExit($message)
{
    header('Content-type:application/json;charset=utf-8');
    echo json_encode($message);
    flush();
    exit(0);
}

function sendErrorAndExit($messageText)
{
    sendJSONandExit(['error' => $messageText]);
}

if (!isset($_POST['action']))
    sendErrorAndExit("Niste odabrali što želite.");

$ss = new SplannerService();
$action = $_POST['action'];
$idUser = $_SESSION['id_user'] ?? null;
$tip = $_SESSION['tip_korisnika'] ?? null;

switch ($action) {

    case 'get_grupe_user':
        
        $targetId = ($_POST['user_id'] === $_SESSION['id_user']) ? $idUser : intval($_POST['user_id']);
        $grupe = $ss->getGrupeForUser($targetId);
        
        // Get activity details for each group
        $detalji_akt = array();
        foreach ($grupe as $g) {
            $detalji_akt[] = $ss->getAktZaGrupu($g['fk_id_aktivnosti']);
        }
        
        // Return as JSON
        sendJSONandExit([
            'success' => true,
            'grupe' => $grupe,
            'detalji_akt' => $detalji_akt,
            'is_self' => ($_POST['user_id'] === 'self' || $_POST['user_id'] === $_SESSION['id_user'])
        ]);
        break;

    case 'ispisi_se':
        $idAkt = intval($_POST['aktivnost_id']);
        $userKojiIspisujem = $_POST['child_id'] ?? $idUser;
        $ss->ispisiUseraSaAkt($userKojiIspisujem, $idAkt); 
        sendJSONandExit(['success' => true]);
        break;

    case 'get_grupe':
        $idAkt = intval($_POST['aktivnost_id']);
        $grupe = $ss->getGrupeZaAkt($idAkt);
        $sadasnje_vrijeme_php = new DateTime();
        $datum_za_14_dana = clone $sadasnje_vrijeme_php;
        $datum_za_14_dana->modify('next sunday')->modify('next sunday'); //NEDJELJA SLJEDECA
        $datum_za_14_dana->format('Y-m-d');
        $daniTjedna = [
            'Monday'    => 'Ponedjeljak',
            'Tuesday'   => 'Utorak',
            'Wednesday' => 'Srijeda',
            'Thursday'  => 'Četvrtak',
            'Friday'    => 'Petak',
            'Saturday'  => 'Subota',
            'Sunday'    => 'Nedjelja'
        ];
        ob_start(); //NAPORAVEI HANDLANJE ZA DATUM I ZA OICAN DAN U TJEDNU ZA UIS/EDIT AKTIVNOSTI
        foreach ($grupe as $g): 
            $azurniTermini = $ss->getAzurniTerminiZaGrupu($g['id_grupe']);?>
            <div class="grupa kosarcica" data-grupa-id="<?= $g['id_grupe'] ?>">
            <h4 class="ime ime-grupe-link" onclick="window.location.href='index.php?rt=aktivnosti/grupa&id=<?php echo $g['id_grupe']; ?>'"><?= htmlspecialchars($g['ime']) ?></h4>
            <br>
            <span class="termin"><?php
            foreach($azurniTermini as $termin):
                $flag=0;
                $dan_u_tj=date('l', strtotime($termin['datum_origin']));
                if(new DateTime($termin['datum_origin']) >= $sadasnje_vrijeme_php && new DateTime($termin['datum_origin']) <= $datum_za_14_dana ||
                new DateTime($termin['datum_origin']) !== null && new DateTime($termin['datum_origin']) !==null && new DateTime($termin['datum_novi']) >= $sadasnje_vrijeme_php && new DateTime($termin['datum_novi']) <= $datum_za_14_dana  ){
                    if($termin['fk_id_redovni_termini']!==null){
                    $vrijemePoc = DateTime::createFromFormat('H:i:s', $termin['vrijeme_poc_stari']);
                    $satMinut = $vrijemePoc->format('H:i');
                    $vrijemeKraj = DateTime::createFromFormat('H:i:s', $termin['vrijeme_kraj_stari']);
                    $satMinutKraj = $vrijemeKraj->format('H:i');
                    $datumFormatiran = (new DateTime($termin['datum_origin']))->format('d-m');
                    echo '<span class="termin" valueAzur='.$termin['id_azurni_termini'].' valueRed='.$termin['fk_id_redovni_termini'].'>'.$daniTjedna[$dan_u_tj].', '.$datumFormatiran.': '.$satMinut.'-'.$satMinutKraj.', '.$termin['dvorana'];
                    }
                    else{
                        $vrijemePoc = DateTime::createFromFormat('H:i:s', $termin['vrijeme_poc_stari']);
                            $satMinut = $vrijemePoc->format('H:i');
                            $vrijemeKraj = DateTime::createFromFormat('H:i:s', $termin['vrijeme_kraj_stari']);
                            $satMinutKraj = $vrijemeKraj->format('H:i');
                            $datumFormatiran = (new DateTime($termin['datum_origin']))->format('d-m');
                            echo '<b>Izvanredno:</b> '. $daniTjedna[date('l', strtotime($termin['datum_origin']))] .', '. $datumFormatiran. ' '.$satMinut.'-'.$satMinutKraj.', '.$termin['dvorana'];
                            $dan_u_tj=date('l', strtotime($termin['datum_origin']));
                            
                            if($tip==='trener'){
                                echo '<button class="obrisi-termin-btn" data-id="'.$termin['id_azurni_termini'].'" style="color:white;">🗑️ Obriši</button>';
                                echo '<br>';
                            }
                            continue;
                    }
                    if($termin['datum_novi']!==null){
                        $vrijemePoc = DateTime::createFromFormat('H:i:s', $termin['vrijeme_poc_novi']);
                        $satMinut = $vrijemePoc->format('H:i');
                        $vrijemeKraj = DateTime::createFromFormat('H:i:s', $termin['vrijeme_kraj_novi']);
                        $satMinutKraj = $vrijemeKraj->format('H:i');
                        $datumFormatiran = (new DateTime($termin['datum_origin']))->format('d-m');
                        echo ' ➡️ <b>Izvanredno:</b> '. $daniTjedna[date('l', strtotime($termin['datum_novi']))] .', '. $datumFormatiran. ' '.$satMinut.'-'.$satMinutKraj.', '.$termin['dvorana'];
                        $dan_u_tj=date('l', strtotime($termin['datum_novi']));
                    }
                    if ($tip === 'trener'):
                    echo '<button class="uredi-termin-btn">✏️ Uredi termin</button></span>';
                    echo '<button class="obrisi-termin-btn" data-id="'.$termin['id_azurni_termini'].'" style="color:white;">🗑️ Obriši</button>';
                    echo '<br>';
                    endif;
                }
            endforeach;
            ?></span> 
            <?php if ($tip === 'trener'): ?>
                <button class="dodaj-termin-btn">✏️ Dodaj termin</button>
                <button class="objavi-obavijest-btn">Dodaj obavijest</button>
            <?php endif; ?>
            </div>
        <?php endforeach;
        $html = ob_get_clean();
        sendJSONandExit(['html' => $html]);
        break;

        case 'create_termin':
            if ($tip !== 'trener') sendErrorAndExit("Nemate pristup.");
            $required = ['tip_termina', 'id_grupe','id_trener', 'datum', 'vrijeme_poc', 'vrijeme_kraj', 'dvorana'];
            foreach ($required as $r)
                if (!isset($_POST[$r]))
                    sendErrorAndExit("Nedostaje podatak: $r");
        
            $tip_termina = $_POST['tip_termina'];
            $id = intval($_POST['id_grupe']);
            $datum = $_POST['datum'];
            $trener = intval($_POST['id_trener']);
            $vrijeme_poc = $_POST['vrijeme_poc'];
            $vrijeme_kraj = $_POST['vrijeme_kraj'];
            $dvorana = $_POST['dvorana'];
            // if($tip_termina==='redovan'){
            $ss->makeTerminZaGrupu($id,$datum,$trener,$vrijeme_poc,$vrijeme_kraj,$dvorana,$tip_termina);
            // }
            // else{
            // }
            sendJSONandExit(['success' => true]);
            break;

        case 'update_termin':
            if ($tip !== 'trener') sendErrorAndExit("Nemate pristup.");
        
            $required = ['tip_termina', 'id_red','id_azur', 'datum', 'vrijeme_poc', 'vrijeme_kraj', 'dvorana'];
            foreach ($required as $r)
                if (!isset($_POST[$r]))
                    sendErrorAndExit("Nedostaje podatak: $r");
        
            $tip_termina = $_POST['tip_termina'];
            $id_red = intval($_POST['id_red']);
            $datum = $_POST['datum'];
            $vrijeme_poc = $_POST['vrijeme_poc'];
            $vrijeme_kraj = $_POST['vrijeme_kraj'];
            $dvorana = $_POST['dvorana'];
            $grupaId=$_POST['grupa_id'];
            $id_azur=intval($_POST['id_azur']);
            //tu updateaj i redovni i azurni ako je rijec o redovnoj promjeni termina - jedinstveno je azurni odreden sa id_Azur i fk_id_red
            try {
                if ($tip_termina === 'redovan'){
                    $ss->updateRedovniTermin($id_red, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana,$id_azur);
                }
                    elseif ($tip_termina === 'izvanredan')
                    $ss->updateAzurniTermin($id_red, $id_azur, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana,1);
                else
                    sendErrorAndExit("Nepoznat tip termina.");
        
            $grupa=$ss->getGrupa($grupaId);
                sendJSONandExit(['success' => true, 'ime' => $grupa['ime']]); //TU DODAJ termin onako napisano kao u stvaranju grupe
            } catch (Exception $e) {
                sendErrorAndExit("Greška: " . $e->getMessage());
            }
            break;


    case 'create_grupa':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup.");
    
        $aktivnostId = $_POST['aktivnost_id'] ?? null;
        $ime = $_POST['ime'] ?? '';
        $cijena = floatval($_POST['cijena']);
        $dobMin = $_POST['dobMin'] === "" ? null : intval($_POST['dobMin']);
        $dobMax = $_POST['dobMax'] === "" ? null : intval($_POST['dobMax']);
        $spol = $_POST['spol'] ?? null;
    
        if (!$aktivnostId || !$ime) 
            sendErrorAndExit("Nedostaju obavezni podaci.");
    
        try {
            $ss->createGrupa($aktivnostId, $ime, $cijena, $dobMin,$dobMax, $spol);
            sendJSONandExit(['success' => true]);
        } catch (Exception $e) {
            sendErrorAndExit("Greška: " . $e->getMessage());
        }
        break;
            


    case 'update_aktivnost':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup ovome.");
        $id = intval($_POST['id']);
        $ime = trim($_POST['ime']);
        $opis = trim($_POST['description']);
        $grad = $_POST['grad'];
        $ss->updateAktivnost($id, $ime, $opis, $grad);
        sendJSONandExit(['success' => true]);
        break;

    case 'create_aktivnost':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup ovome.");
        $ime = trim($_POST['ime']);
        $opis = trim($_POST['description']);
        $grad = $_POST['grad'];
        $ss->upisAkt($idUser, $ime, $opis, $grad);
        sendJSONandExit(['success' => true]);
        break;
    
    case 'delete_aktivnost':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup ovome.");
        $id = intval($_POST['id']);
        $ss->deleteAktivnost($id); 
        sendJSONandExit(['success' => true]);
        break;

    case 'delete_termin':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup ovome.");
        $id = intval($_POST['id']);
        $ss->deleteTermin($id); 
        sendJSONandExit(['success' => true]);
        break;
    
    case 'dodaj_obavijest':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup ovome.");
        $id_grupe = intval($_POST['id_grupe']);
        $comment = trim($_POST['comment']);
        if (empty($comment))
            sendErrorAndExit("Obavijest ne može biti prazna.");
        dodajObavijestPosaljiMailove($id_grupe, $comment);
        sendJSONandExit(['success' => true]);
        break;

    default:
        sendErrorAndExit("Nepoznata radnja.");
}