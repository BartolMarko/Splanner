<?php

require_once __DIR__ . '/../model/splannerservice.class.php';
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

    case 'get_aktivnosti':
        $targetId = ($_POST['user_id'] === 'self') ? $idUser : intval($_POST['user_id']);
        $aktivnosti = $ss->getAktivnostiZaUser($targetId); //TODO u splannerservice

        ob_start(); //ovo stavlja sve echo-e, print-ove i stvari van php tag-a u buffer, umjesto da ih isprinta
        foreach ($aktivnosti as $a): ?>
            <div class="aktivnost" data-aktivnost-id="<?= $a['id_aktivnosti'] ?>">
                <h3 class="naziv"><?= htmlspecialchars($a['ime']) ?></h3>
                <p class="opis"><?= htmlspecialchars($a['description']) ?></p>
                <?php if ($tip === 'trener'): ?>
                    <button class="uredi-btn" data-id="<?= $a['id_aktivnosti'] ?>">Uredi aktivnost</button>
                    <button class="toggle-grupe-btn" data-id="<?= $a['id_aktivnosti'] ?>">➤ Prikaži grupe</button>
                    <div class="grupe" id="grupe_<?= $a['id_aktivnosti'] ?>" style="display:none;"></div>
                <?php elseif ($tip === 'roditelj' && $_POST['user_id'] === 'self'): ?>
                    <button class="ispisi-btn" data-id="<?= $a['id_aktivnosti'] ?>">Ispiši se</button>
                <?php elseif ($tip === 'roditelj'): ?>
                    <button class="ispisi-btn" data-id="<?= $a['id_aktivnosti'] ?>" data-child="<?= $targetId ?>">Ispiši dijete</button>
                <?php endif; ?>
            </div>
        <?php endforeach;
        $html = ob_get_clean(); //i ovdje sve to strpam u $html, da mogu poslati stvarnoj stranici za ispis ( ne da mi se ispise tu)
        sendJSONandExit(['html' => $html]);
        break;

    case 'ispisi_se':
        $idAkt = intval($_POST['aktivnost_id']);
        $userKojiIspisujem = $_POST['child_id'] ?? $idUser;
        $ss->ispisiUseraSaAkt($userKojiIspisujem, $idAkt); //TODO u splannerservice
        sendJSONandExit(['success' => true]);
        break;

    case 'get_grupe':
        $idAkt = intval($_POST['aktivnost_id']);
        $grupe = $ss->getGrupeZaAkt($idAkt);
        $sadasnje_vrijeme_php = new DateTime();
        $datum_za_14_dana = clone $sadasnje_vrijeme_php;
        $datum_za_14_dana->modify('next sunday'); //NEDJELJA SLJEDECA
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
            $redovniTermini = $ss->getRedovniTerminiZaGrupu($g['id_grupe']);
            $azurniTermini = $ss->getAzurniTerminiZaGrupu($g['id_grupe']);?>
            <div class="grupa" data-grupa-id="<?= $g['id_grupe'] ?>">
            <strong class="ime"><?= htmlspecialchars($g['ime']) ?></strong> : 
            <br>
            <span class="termin"><?php
            foreach($azurniTermini as $termin):
                $dan_u_tj=date('l', strtotime($termin['datum_origin']));
                if(new DateTime($termin['datum_origin']) >= $sadasnje_vrijeme_php && new DateTime($termin['datum_origin']) <= $datum_za_14_dana){
                    echo '<span class="termin" valueAzur='.$termin['id_azurni_termini'].' valueRed='.$termin['fk_id_redovni_termini'].'>'.$daniTjedna[$dan_u_tj].': '.$termin['vrijeme_poc_stari'].'-'.$termin['vrijeme_kraj_stari'];
                    if($termin['datum_novi']!==null){
                        echo ' (Izvanredno: '. $daniTjedna[date('l', strtotime($termin['datum_novi']))] .', '. $termin['datum_novi']. ' '.$termin['vrijeme_poc_novi'].'-'.$termin['vrijeme_kraj_novi'].')';
                        $dan_u_tj=date('l', strtotime($termin['datum_novi']));
                        echo '<span>'.'</span>';
                    }
                    if ($tip === 'trener'):
                    echo '<button class="uredi-termin-btn">✏️ Uredi termin</button></span>';
                    echo '<br>';
                    endif;
                }
            endforeach;
            ?></span> 
            <?php if ($tip === 'trener'): ?>
                <button class="dodaj-termin-btn">✏️ Dodaj termin</button>
            <?php endif; ?>
            </div>
        <?php endforeach;
        $html = ob_get_clean();
        sendJSONandExit(['html' => $html]);
        break;

        case 'create_termin':
            if ($tip !== 'trener') sendErrorAndExit("Nemate pristup.");
            $required = ['tip_termina', 'id_grupe','id_trener', 'datum', 'vrijeme_poc', 'vrijeme_kraj', 'dvorana', 'comment'];
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
            $comment = $_POST['comment'];

            $ss->makeTerminZaGrupu($id,$datum,$trener,$vrijeme_poc,$vrijeme_kraj,$dvorana,$comment,$tip_termina);
            sendJSONandExit(['success' => true]);
            break;

        case 'update_termin':
            if ($tip !== 'trener') sendErrorAndExit("Nemate pristup.");
        
            $required = ['tip_termina', 'id_red','id_azur', 'datum', 'vrijeme_poc', 'vrijeme_kraj', 'dvorana', 'comment'];
            foreach ($required as $r)
                if (!isset($_POST[$r]))
                    sendErrorAndExit("Nedostaje podatak: $r");
        
            $tip_termina = $_POST['tip_termina'];
            $id_red = intval($_POST['id_red']);
            $datum = $_POST['datum'];
            $vrijeme_poc = $_POST['vrijeme_poc'];
            $vrijeme_kraj = $_POST['vrijeme_kraj'];
            $dvorana = $_POST['dvorana'];
            $comment = $_POST['comment'];
            $id_azur=intval($_POST['id_azur']);
            //tu updateaj i redovni i azurni ako je rijec o redovnoj promjeni termina - jedinstveno je azurni odreden sa id_Azur i fk_id_red
            try {
                if ($tip_termina === 'redovan'){
                    $ss->updateRedovniTermin($id_red, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment);
                    $ss->updateAzurniTermin($id_red, $id_azur, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment,0);
                }
                    elseif ($tip_termina === 'izvanredan')
                    $ss->updateAzurniTermin($id_red, $id_azur, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment,1);
                else
                    sendErrorAndExit("Nepoznat tip termina.");
        
                sendJSONandExit(['success' => true]); //TU DODAJ IME I TERMIN
            } catch (Exception $e) {
                sendErrorAndExit("Greška: " . $e->getMessage());
            }
            break;


    case 'create_grupa':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup.");
    
        $aktivnostId = $_POST['aktivnost_id'] ?? null;
        $ime = $_POST['ime'] ?? '';
    
        if (!$aktivnostId || !$ime) {
            sendErrorAndExit("Nedostaju obavezni podaci.");
        }
    
        try {
            $ss->createGrupa($aktivnostId, $ime);
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
        $cijena = floatval($_POST['cijena']);
        $ss->updateAktivnost($id, $ime, $opis, $cijena);
        sendJSONandExit(['success' => true]);
        break;

    case 'create_aktivnost':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup ovome.");
        $ime = trim($_POST['ime']);
        $opis = trim($_POST['description']);
        $cijena = floatval($_POST['cijena']);
        $dobMin = $_POST['dobMin'] ?? null;
        $dobMax = $_POST['dobMax'] ?? null;
        $ss->upisAkt($idUser, $ime, $opis, $cijena,$dobMin,$dobMax);
        sendJSONandExit(['success' => true]);
        break;
    
    case 'delete_aktivnost':
        if ($tip !== 'trener') sendErrorAndExit("Nemate pristup ovome.");
        $id = intval($_POST['id']);
        $ss->deleteAktivnost($id); //TODO u splannerservice
        sendJSONandExit(['success' => true]);
        break;

    default:
        sendErrorAndExit("Nepoznata radnja.");
}