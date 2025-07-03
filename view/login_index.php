<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<?php if (isset($error)) echo '<p style="color:red;">' . $error . '</p>'; ?>

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
</div>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>