<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<form class="pretraga" method="POST" action="<?php echo __SITE_URL; ?>/index.php?rt=pretraga">

    <label>Naziv aktivnosti:</label><br>
    <input type="text" name="ime" placeholder="Unesite aktivnost koja Vas zanima">
    <br>
    <label>Grad:</label><br>
    <input type="text" name="grad" placeholder="Unesite ime grada">
    <br>
    <label>Spol:</label><br>
    <select name="spol">
        <option value="" disabled selected>Odaberi</option>
        <option value="mješovito">Mješovito</option>
        <option value="muško">Muško</option>
        <option value="žensko">Žensko</option>
    </select>
    <br>
    <!-- promjena -->
    <label>Uzrast:</label><br>
    <input type="number" name="uzrast" placeholder="Unesite dob člana kojeg želite upisati" min="0" max="120">
    <br>
    <button type="submit">🔍 Pretraži</button>

</form>


<?php require_once __SITE_PATH . '/view/_footer.php'; ?>