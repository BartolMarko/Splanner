<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<div class="login-form">
    <p class="centered-text">
        Već imate korisnički račun?<br><br>
        <a href="<?php echo __SITE_URL; ?>/index.php?rt=login" class="button-link">Prijava</a>.
    </p>

    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=login/registracija">

        Korisničko ime:
        <br>
        <input type="text" name="username" required>
        <br><br>
        Lozinka:
        <br>
        <input type="password" name="password" required>
        <br><br>
        Ponovite lozinku:
        <br>
        <input type="password" name="password_again" required>
        <br><br>
        Email:
        <br>
        <input type="text" name="email" required>
        <br><br>
        OIB:
        <br>
        <input type="text" name="oib" required>
        <br><br>
        Vrsta korisnika:
        <br>
        <select id="uloga" name="uloga" required>
        <option value="" disabled selected>-- Odaberi --</option>
        <option value="roditelj">Korisnik</option>
        <option value="trener">Trener</option>
        </select>
        <br><br>
        Spol:
        <br>
        <select id="spol" name="spol" required>
        <option value="" disabled selected>-- Odaberi --</option>
        <option value="žensko">Žensko</option>
        <option value="muško">Muško</option>
        </select>
        <br><br>
        Datum rođenja:
        <br>
        <input type="date" name="datum">
        <button type="submit">Registracija</button>
    </form>

    <?php if (isset($error)) echo '<p class="centered-text" style="color:red; margin-top: 20px;">' . $error . '</p>'; ?>
</div>


<?php require_once __SITE_PATH . '/view/_footer.php'; ?>