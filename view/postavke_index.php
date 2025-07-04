<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<h2>Postavke računa</h2>

<?php if (!empty($poruka)) echo "<p style='color:red;'>".$poruka."</p>"; ?>

<h2>Unesite novo korisničko ime</h2>

<form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/promjenaUsername">
    <label>Novo korisničko ime:</label><br>
    <input type="text" name="novo_username" required><br><br>
    <input type="submit" value="Promijeni korisničko ime">
</form>



<h2>Promjena lozinke</h2>

<form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/promjenaLozinke">
    <label>Stara lozinka:</label><br>
    <input type="password" name="stara_lozinka" required><br><br>

    <label>Nova lozinka:</label><br>
    <input type="password" name="nova_lozinka" required><br><br>

    <label>Ponovi novu lozinku:</label><br>
    <input type="password" name="nova_lozinka2" required><br><br>

    <input type="submit" value="Promijeni lozinku">
</form>


<?php require_once __SITE_PATH . '/view/_footer.php'; ?>

