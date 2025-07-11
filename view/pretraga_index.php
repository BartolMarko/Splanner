<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<form class="pretraga" method="POST" action="<?php echo __SITE_URL; ?>/index.php?rt=pretraga">

    <label>Naziv grupe:</label><br>
    <input type="text" name="ime" placeholder="Unesite sport koji Vas zanima">
    <br>
    <label>Grad:</label><br>
    <input type="text" name="grad" placeholder="Unesite ime grada">
    <br>
    <label>Spol:</label><br>
    <select name="spol">
        <option value="" disabled selected>Odaberi</option>
        <option value="oboje">Mješovito</option>
        <option value="muško">Muško</option>
        <option value="žensko">Žensko</option>
    </select>
    <br>
    <!-- promjena -->
    <label>Uzrast:</label><br>
    <input type="number" name="uzrast" min="0" max="120">
    <br>
    <button type="submit">🔍 Pretraži</button>

</form>


<?php require_once __SITE_PATH . '/view/_footer.php'; ?>