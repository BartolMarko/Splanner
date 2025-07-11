<?php require_once __SITE_PATH . '/view/_header.php'; ?>


<div id="dodatniDetaljiAktivnost">
    <ul>
        <li>Aktivnost: <?php echo $naziv_aktivnosti; ?></li>
        <li>Opis:   <?php echo $aktivnost_detalji['description']; ?> </li>
        <li>Trener: <?php echo $ime_trenera; ?></li>
        <li>Grad: <?php echo $aktivnost_detalji['grad']; ?></li>
    </ul>
</div>
<br>


<button id="btnDetalji">Prikaži detalje</button>

<div id="dodatniDetaljiGrupa" style="display: none; margin-top: 1em;">
    <h3>Dodatne informacije o grupi</h3>
    <ul>
        <li>Cijena:  <?php echo $grupa_detalji['cijena']; ?> €</li>
        <li>Spol:  <?php echo $grupa_detalji['spol']; ?></li>
        <li>Uzrast:  <?php echo $grupa_detalji['uzrast_od'] . ' - ' . $grupa_detalji['uzrast_do']; ?></li>
    </ul>
</div>

<?php if ($_SESSION['id_user'] !== $aktivnost_detalji['fk_id_trenera']): ?> <!-- ako nije trener -->
<!-- UPIS / ISPIS ČLANOVA - KORISNICI -->
<?php if (!empty($imenaPovezanihUGrupi)): ?>
    <h3 class="toggle-header">Ispis</h3>
    <div class="toggle-content">
        <p>Ovdje možete ispisati članove iz grupe:</p>
        <?php foreach ($imenaPovezanihUGrupi as $id_korisnika => $clan): ?>
            <form method="post" action="<?= __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $_GET['id'] ?>" style="display:inline;" onsubmit="return confirm('Jeste li sigurni da želite ispisati ovog člana?');">
                <input type="hidden" name="id_clana_ispis" value="<?= (int)$id_korisnika ?>">
                <button id="btnDetalji" type="submit"><?= htmlspecialchars($clan) ?></button>
            </form>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h3 class="toggle-header">Upis</h3>
<div class="toggle-content">
    <?php if (!empty($clanoviZaUpis )): ?> <!-- ako ima onih koji zadovoljavaju uvjete -->
        <?php foreach ($clanoviZaUpis as $id_korisnika => $clan): ?>
            <form method="post" action="<?= __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $_GET['id'] ?>" style="display:inline;" onsubmit="return confirm('Jeste li sigurni da želite upisati ovog člana?');">
                <input type="hidden" name="id_clana_upis" value="<?= (int)$id_korisnika ?>">
                <button id="btnDetalji" type="submit"><?= htmlspecialchars($clan) ?></button>
            </form>
        <?php endforeach; ?>
    <?php elseif(!empty($povezaniZaUpis)): ?>
        <p>Vaši članovi ne zadovoljavaju uvjete za upis.</p>
    <?php else: ?>
        <p> Nemate članova za upis. </p>
    <?php endif; ?>
</div>
<?php endif; ?>


<!-- OBAVIJESTI - TRENER + UPISANI (ili njihovi roditelji) -->
<?php if ($_SESSION['id_user'] === $aktivnost_detalji['fk_id_trenera'] || !empty($imenaPovezanihUGrupi)): ?>
    <h3 class="toggle-header">Obavijesti</h3>
    <div class="toggle-content">
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
    </div>
<?php endif; ?>

<!-- UNOS OBAVIJESTI + ČLANOVI GRUPE - TRENER -->
<?php if ($_SESSION['id_user'] === $aktivnost_detalji['fk_id_trenera']): ?>
    <h3 class="toggle-header">Objavi novu obavijest</h3>
    <div class="toggle-content">
        <form method="post" action="<?= __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $grupa_detalji['id_grupe'] ?>" class="forma-obavijest">
            <input type="hidden" name="id_grupe" value="<?php echo $grupa_detalji['id_grupe']; ?>">
            <label for="comment">Tekst obavijesti:</label><br>
            <textarea name="comment" id="comment" rows="4" required></textarea><br>
            <button type="submit">Objavi obavijest</button>
        </form>
    </div>
    <h3 class="toggle-header">Članovi grupe</h3>
    <div class="toggle-content">
        <?php foreach ($imenaClanovaGrupe as $clan): ?>
            <p><?= htmlspecialchars($clan) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

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
    $('.toggle-content').hide();
    $('.toggle-header').click(function(){
        $('.toggle-content').not($(this).next()).slideUp();
        $(this).next().slideToggle();
        $('.toggle-header').not(this).removeClass('active');
        $(this).toggleClass('active');
    });
});
</script>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>
