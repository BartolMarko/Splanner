<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<div>
    <?php echo $aktivnost_detalji['description']; ?>
</div>

<div id="dodatniDetaljiAktivnost">
    <ul>
        <li>Trener: <?php echo $ime_trenera; ?></li>
        <li>Grad: <?php echo $aktivnost_detalji['grad']; ?></li>
    </ul>
</div>

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

<h3>Obavijesti</h3>

<?php if (count($obavijestiList) == 0): ?>
    <p>Nema novih obavijesti.</p>
<?php else: ?>
    <?php foreach( $obavijestiList as $obavijest ): ?>
        <div class="obavijest" onclick="window.location.href='index.php?rt=aktivnosti/grupa&id=<?= $obavijest->id_grupe_fk ?>'">
            <div class="obavijest-header">
                <span class="obavijest-naziv"><?= htmlspecialchars($obavijest->aktivnost_ime) ?>, <?= htmlspecialchars($obavijest->ime) ?></span>
                <span class="obavijest-vrijeme"><?= $obavijest->datum ?> <?= $obavijest->vrijeme ?></span>

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

<?php if ($_SESSION['id_user'] === $aktivnost_detalji['fk_id_trenera']): ?>
    <form method="post" action="<?= __SITE_URL . '/index.php?rt=aktivnosti/grupa&id=' . $obavijest->id_grupe_fk ?>" class="forma-obavijest">
        <input type="hidden" name="id_grupe" value="<?php echo $grupa_detalji['id_grupe']; ?>">
        
        <label for="comment">Tekst obavijesti:</label><br>
        <textarea name="comment" id="comment" rows="4" required></textarea><br>

        <button type="submit">Objavi obavijest</button>
    </form>
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
