<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<div class="login-form">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=login">

        Unesite korisničko ime:
        <input type="text" name="username" required>
        <br>
        Unesite lozinku:
        <input type="password" name="password" required>
        <br>
        <button type="submit">Prijava</button>
    </form>

    <p class="centered-text">
        <br>
        Ako i dalje niste dio našeg tima, sada je pravo vrijeme da to promijenimo! <br><br>
        <a href="<?php echo __SITE_URL; ?>/index.php?rt=login/registracija" class="button-link">Registracija</a>
    </p>

    <?php if (isset($error)) echo '<p class="centered-text" style="color:red; margin-top: 20px;">' . $error . '</p>'; ?>
</div>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>