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

    <label>Dob od:</label>
    <input type="number" name="uzrast_od" min="0">

    <label>Dob do:</label>
    <input type="number" name="uzrast_do" min="0">

    <button type="submit">🔍 Pretraži</button>

</form>


<?php require_once __SITE_PATH . '/view/_footer.php'; ?>