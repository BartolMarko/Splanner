<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<h2>Postavke računa</h2>

<h2>Unesite novo korisničko ime</h2>

<form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/promjenaUsername">
    <label>Novo korisničko ime:</label><br>
    <input type="text" name="novo_username" required><br><br>
    <input type="submit" value="Promijeni korisničko ime">
</form>

<?php if (!empty($poruka)) echo "<p style='color:red;'>".$poruka."</p>"; ?>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>

