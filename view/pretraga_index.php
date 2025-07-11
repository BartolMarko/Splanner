<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<form method="POST" action="<?php echo __SITE_URL; ?>/index.php?rt=pretraga">

    <label>Naziv grupe:</label>
    <input type="text" name="ime" placeholder="npr. Karate juniori">

    <label>Grad:</label>
    <input type="text" name="grad" placeholder="npr. Zagreb">

    <label>Spol:</label>
    <select name="spol">
        <option value="oboje">Bilo koji</option>
        <option value="muško">Muško</option>
        <option value="žensko">Žensko</option>
    </select>

    <!-- promjena -->
    <label>Uzrast:</label>
    <input type="number" name="uzrast" min="0" max="120">

    <button type="submit">🔍 Pretraži</button>

</form>


<?php require_once __SITE_PATH . '/view/_footer.php'; ?>