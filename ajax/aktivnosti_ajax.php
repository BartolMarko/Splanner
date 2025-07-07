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
                <p class="cijena">Cijena: <?= htmlspecialchars($a['cijena']) ?> kn</p>
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

        ob_start(); //ovdje tu CU MORAT DOHVATIT SVE TERMINE ZA GRUPU, I AK JE TRENER POSTAVIT OVO UREDI TERMINZA SVAKI I NA KRAJU STAVIT DODAJ NOVI TERMIN
        foreach ($grupe as $g): ?>
            <div class="grupa" data-grupa-id="<?= $g['id_grupe'] ?>">
            <strong class="ime"><?= htmlspecialchars($g['ime']) ?></strong> - 
            <span class="termin"><?= htmlspecialchars($g['termin']) ?></span> 
            <?php if ($tip === 'trener'): ?>
                <button class="uredi-termin-btn">✏️ Uredi termin</button>
            <?php endif; ?>
            </div>
        <?php endforeach;
        $html = ob_get_clean();
        sendJSONandExit(['html' => $html]);
        break;

        case 'update_termin':
            if ($tip !== 'trener') sendErrorAndExit("Nemate pristup.");
        
            $required = ['tip_termina', 'id_termina', 'datum', 'vrijeme_poc', 'vrijeme_kraj', 'dvorana', 'comment'];
            foreach ($required as $r)
                if (!isset($_POST[$r]))
                    sendErrorAndExit("Nedostaje podatak: $r");
        
            $tip_termina = $_POST['tip_termina'];
            $id = intval($_POST['id_termina']);
            $datum = $_POST['datum'];
            $vrijeme_poc = $_POST['vrijeme_poc'];
            $vrijeme_kraj = $_POST['vrijeme_kraj'];
            $dvorana = $_POST['dvorana'];
            $comment = $_POST['comment'];
        
            try {
                if ($tip_termina === 'redovni')
                    $ss->updateRedovniTermin($id, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment);
                elseif ($tip_termina === 'azurni')
                    $ss->updateAzurniTermin($id, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment);
                else
                    sendErrorAndExit("Nepoznat tip termina.");
        
                sendJSONandExit(['success' => true]);
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