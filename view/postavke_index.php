<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<!-- <h2>Postavke računa</h2> -->

<?php if (!empty($poruka)): ?>
    <?php if ($tip_poruke === 'greska'): ?>
        <p style="color:red;"><?php echo $poruka; ?></p>
    <?php else: ?>
        <p style="color:green;"><?php echo $poruka; ?></p>
    <?php endif; ?>
<?php endif; ?>

<!-- SVE NASLOVE STAVLJAMO S KLASOM toggle-header -->
<h3 class="toggle-header">Promjena korisničkog imena</h3>
<div class="toggle-content">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/promjenaUsername">
        <label>Novo korisničko ime:</label><br>
        <input type="text" name="novo_username" required><br><br>
        <input type="submit" value="Promijeni korisničko ime">
    </form>
</div>

<?php if ($_SESSION['tip_korisnika'] === 'roditelj'): ?>

<h3 class="toggle-header">Promjena lozinke</h3>
<div class="toggle-content">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/promjenaLozinke">
        <label>Stara lozinka:</label><br>
        <input type="password" name="stara_lozinka" required><br><br>

        <label>Nova lozinka:</label><br>
        <input type="password" name="nova_lozinka" required><br><br>

        <label>Ponovi novu lozinku:</label><br>
        <input type="password" name="nova_lozinka2" required><br><br>

        <input type="submit" value="Promijeni lozinku">
    </form>
</div>

<h3 class="toggle-header">Dodavanje člana obitelji</h3>
<div class="toggle-content">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/dodajDijete">
        <label>Korisničko ime:</label><br>
        <input type="text" name="username" required><br><br>

        <label>OIB:</label><br>
        <input type="text" name="oib" required><br><br>

        <label>Lozinka:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Dodaj dijete">
    </form>
</div>

<h3 class="toggle-header">Brisanje računa</h3>
<div class="toggle-content">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/obrisiRacun" onsubmit="return confirm('Jeste li sigurni da želite obrisati svoj račun? Ova akcija je nepovratna!');">
        <input type="submit" value="Obriši moj račun trajno" style="background-color: red; color: white;">
    </form>
</div>

<?php endif; ?>

<!-- JQUERY SCRIPT -->
<script>

$(document).ready(function(){
    // Sakrij sve sadržaje
    $('.toggle-content').hide();

    // Klik na header
    $('.toggle-header').click(function(){
    $('.toggle-content').not($(this).next()).slideUp();
    $(this).next().slideToggle();
    $('.toggle-header').not(this).removeClass('active');
    $(this).toggleClass('active');
});

});
</script>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>
