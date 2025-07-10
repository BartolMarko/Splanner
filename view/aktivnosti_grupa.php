<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<!-- AKTIVNOST -->
<hr style="width: 100%; border: none; border-top: 5px solid #100263;">

<h1><?php echo $title2; ?></h1>
<div>
    <?php echo $aktivnost_detalji['description']; ?>
</div>

<div id="dodatniDetaljiAktivnost">
    <ul>
        <li>Trener: <?php echo $ime_trenera; ?></li>
        <li>Grad: <?php echo $aktivnost_detalji['grad']; ?></li>
    </ul>
</div>
<br>
<hr style="width: 100%; border: none; border-top: 5px solid #100263;">


<!-- GRUPA -->
<h2><?php echo $subtitle; ?></h2>

<button id="btnDetalji">Prikaži detalje</button>

<div id="dodatniDetaljiGrupa" style="display: none; margin-top: 1em;">
    <h3>Dodatne informacije o grupi</h3>
    <ul>
        <li>Cijena:  <?php echo $grupa_detalji['cijena']; ?> €</li>
        <li>Spol:  <?php echo $grupa_detalji['spol']; ?></li>
        <li>Uzrast:  <?php echo $grupa_detalji['uzrast_od'] . ' - ' . $grupa_detalji['uzrast_do']; ?></li>
    </ul>
</div>

<!-- UPIS / ISPIS ČLANOVA - KORISNICI -->
<?php if (!empty($imenaPovezanihUGrupi)):?>
    <h3>Članovi</h3>
    <p>Ovdje možete ispisati članove:</p>
    <?php foreach ($imenaPovezanihUGrupi as $indeks => $clan): ?>
        <form method="post"
            action="<?= __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $_GET['id'] ?>"
            style="display:inline;"
            onsubmit="return confirm('Jeste li sigurni da želite ispisati ovog člana?');">
            <input type="hidden" name="id_clana_ispis" value="<?= $indeks ?>">
            <button id="btnDetalji" type="submit">
                <?= htmlspecialchars($clan) ?>
            </button>
        </form>
    <?php endforeach; ?>

    <?php if (!empty($clanoviZaUpis)): ?>
        <h3>Upiši</h3>
        <?php foreach ($clanoviZaUpis as $indeks => $clan): ?>
            <form method="post"
                action="<?= __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $_GET['id'] ?>"
                style="display:inline;"
                onsubmit="return confirm('Jeste li sigurni da želite upisati ovog člana?');">
                <input type="hidden" name="id_clana_upis" value="<?= $indeks ?>">
                <button id="btnDetalji" type="submit">
                    <?= htmlspecialchars($clan) ?>
                </button>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

<!-- OBAVIJESTI - TRENER + UPISANI (ili njihovi roditelji) -->
<?php if ($_SESSION['id_user'] === $aktivnost_detalji['fk_id_trenera'] || !empty($imenaPovezanihUGrupi)): ?>
    <h3>Obavijesti</h3>

    <?php if (count($obavijestiList) == 0): ?>
        <p>Nema novih obavijesti.</p>
    <?php else: ?>
        <?php foreach( $obavijestiList as $obavijest ): ?>
            <div class="obavijest" onclick="window.location.href='index.php?rt=aktivnosti/grupa&id=<?= $obavijest->id_grupe_fk ?>'">
                <div class="obavijest-header">
                    <span class="obavijest-naziv"><?= htmlspecialchars($obavijest->aktivnost_ime) ?>, <?= htmlspecialchars($obavijest->ime) ?></span>
                    <span class="obavijest-vrijeme"><?= $obavijest->datum ?> <?= $obavijest->vrijeme ?></span>

                    <!-- BRISANJE OBAVIJESTI - SAMO TRENER -->
                    <?php if ($_SESSION['tip_korisnika'] === 'trener'): ?>
                    <form method="post" action="<?= __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $obavijest->id_grupe_fk ?>" style="display:inline" onClick="event.stopPropagation();">
                        <input type="hidden" name="id_obavijesti" value="<?= $obavijest->id_obavijest ?>" />
                        <button type="submit" class="delete-btn" title="Obriši obavijest">🗑️</button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="obavijest-tekst"><?= htmlspecialchars($obavijest->comment) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

<!-- UNOS OBAVIJESTI + ČLANOVI GRUPE - TRENER -->
<?php if ($_SESSION['id_user'] === $aktivnost_detalji['fk_id_trenera']): ?>
    <form method="post" action="<?= __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $obavijest->id_grupe_fk ?>" class="forma-obavijest">
        <input type="hidden" name="id_grupe" value="<?php echo $grupa_detalji['id_grupe']; ?>">
        
        <label for="comment">Tekst obavijesti:</label><br>
        <textarea name="comment" id="comment" rows="4" required></textarea><br>

        <button type="submit">Objavi obavijest</button>
    </form>

    <h3>Članovi grupe</h3>
    <?php foreach ($imenaClanovaGrupe as $clan){
        echo '<p>' . $clan . '</p>';
    }
    ?>
<?php endif; ?>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('#btnDetalji').click(function() {
        $('#dodatniDetaljiGrupa').toggle(); // Prikaži/sakrij div
        // Promijeni tekst gumba
        if ($('#dodatniDetaljiGrupa').is(':visible')) {
            $('#btnDetalji').text('Sakrij detalje');
        } else {
            $('#btnDetalji').text('Prikaži detalje');
        }
    });
});
</script>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>
