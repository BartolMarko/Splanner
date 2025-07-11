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
<h3 class="toggle-header">Promjena korisničkog imena </h3>
<div class="toggle-content">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/promjenaUsername">
        <label>Novo korisničko ime:</label><br>
        <input type="text" name="novo_username" required><br><br>
        <input type="submit" value="Promijeni korisničko ime">
    </form>
</div>

<?php if ($_SESSION['tip_korisnika'] === 'roditelj' || $_SESSION['tip_korisnika'] === 'trener'): ?>

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

<?php endif; ?>

<?php if ($_SESSION['tip_korisnika'] === 'roditelj' || $_SESSION['tip_korisnika'] === 'trener'): ?>

<h3 class="toggle-header">Promjena email adrese</h3>
<div class="toggle-content">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/promjenaEmaila">
        <label>Novi email:</label><br>
        <input type="email" name="novi_email" required><br><br>
        <input type="submit" value="Promijeni email">
    </form>
</div>

<?php endif; ?>


<?php if ($_SESSION['tip_korisnika'] === 'roditelj'): ?>

<h3 class="toggle-header">Dodavanje člana obitelji</h3>
<div class="toggle-content">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/dodajDijete">
        <label>Korisničko ime:</label><br>
        <input type="text" name="username" required><br><br>

        <label>OIB:</label><br>
        <input type="text" name="oib" required><br><br>

        <label>Spol:</label><br>
        <select name="spol" class="odabir_spola" required>
            <option value="">-- Odaberi --</option>
            <option value="muško">Muško</option>
            <option value="žensko">Žensko</option>
        </select><br><br>

        <label>Datum rođenja:</label><br>
        <input type="date" name="datum" class="datum_rodjenja" required><br><br>

        <label>Lozinka:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Ponovi lozinku:</label><br>
        <input type="password" name="password_again" required><br><br>

        <input type="submit" value="Dodaj dijete">
    </form>
</div>

<h3 class="toggle-header">Brisanje člana obitelji</h3>
<div class="toggle-content">
    <?php if (!empty($lista_djece)): ?>
        <p>Odaberite člana kojeg želite obrisati:</p>
        <ul style="list-style:none; padding:0;">
            <?php foreach ($lista_djece as $dijete): ?>
                <li style="margin-bottom:10px; padding:10px; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
                    <span><?php echo htmlspecialchars($dijete['username']); ?></span>
                    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/obrisiDijete"
                          onsubmit="return confirm('Jeste li sigurni da želite obrisati člana <?php echo htmlspecialchars($dijete['username']); ?>?');">
                        <input type="hidden" name="id_djeteta" value="<?php echo $dijete['id_korisnici']; ?>">
                        <button style="background-color:red; color:white; border:none; padding:5px 10px; border-radius:5px;">Obriši</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Nemate dodane članove obitelji.</p>
    <?php endif; ?>
</div>

<h3 class="toggle-header">Obavijesti</h3>
<div class="toggle-content">
    <form method="post" action="<?php echo __SITE_URL; ?>/index.php?rt=postavke/promijeniObavijesti">
        <label>
            <input type="checkbox" name="prima_obavijesti" <?php if (!empty($prima_obavijesti)) echo 'checked'; ?>>
            Želim primati obavijesti na email
        </label><br><br>
        <input type="submit" value="Spremi postavke">
    </form>
</div>

<h3 class="toggle-header">Košarica</h3>
<div class="toggle-content kosarica-content">
<?php
$ukupno = 0;
$ukupnoRoditelj = 0;
$ukupnoDjeca = 0;
?>

<section>
    <h4>Vaše aktivnosti</h4>
    <?php if (!empty($mojeGrupe)): ?>
        <ul class="kosarica-lista">
        <?php foreach ($mojeGrupe as $g): ?>
            <li>
                <a class="naziv-grupe" href="<?= __SITE_URL ?>/index.php?rt=aktivnosti/grupa&id=<?= $g['id_grupe'] ?>&from=postavke">
                    <?php echo htmlspecialchars($g['aktivnost_ime']); ?> – <?php echo htmlspecialchars($g['ime']); ?>
                </a>
                <span class="cijena"><?php echo $g['cijena']; ?> €</span>
                <?php 
                $ukupno += $g['cijena']; 
                $ukupnoRoditelj += $g['cijena'];
                ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="prazno">Nemate upisanih aktivnosti.</p>
    <?php endif; ?>
</section>


<?php if (!empty($djecaGrupe)): ?>
    <?php foreach ($djecaGrupe as $ime => $grupe): ?>
        <section>
            <h4>Aktivnosti za dijete: <?php echo htmlspecialchars($ime); ?></h4>
            <?php if (!empty($grupe)): ?>
                <ul class="kosarica-lista">
                <?php foreach ($grupe as $g): ?>
                    <li>
                        <a class="naziv-grupe" href="<?= __SITE_URL ?>/index.php?rt=aktivnosti/grupa&id=<?= $g['id_grupe'] ?>&from=postavke">
                            <?php echo htmlspecialchars($g['aktivnost_ime']); ?> – <?php echo htmlspecialchars($g['ime']); ?>
                        </a>
                        <span class="cijena"><?php echo $g['cijena']; ?> €</span>
                        <?php 
                        $ukupno += $g['cijena']; 
                        $ukupnoDjeca += $g['cijena'];
                        ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="prazno">Nema aktivnosti.</p>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<div class="ukupno-iznos">
    <p><strong>Ukupno za Vaše aktivnosti:</strong> <?php echo $ukupnoRoditelj; ?> €</p>
    <p><strong>Ukupno za djecu:</strong> <?php echo $ukupnoDjeca; ?> €</p>
    <h3>Sveukupno za platiti: <?php echo $ukupno; ?> €</h3>
</div>
</div>
<?php endif; ?>


<?php if ($_SESSION['tip_korisnika'] === 'trener'): ?>
    <h3 class="toggle-header">Zarada</h3>
    <div class="toggle-content">
        <p>Vaša mjesečna zarada iz svih grupa je:</p>
        <h2 style="color:green;"><?php echo number_format($ukupna_zarada, 2); ?> €</h2>
        <?php if (!empty($zaradaGrupe)): ?>
            <table style="width:100%; border-collapse: collapse; margin-top:15px;">
                <thead>
                    <tr>
                        <th style="border:1px solid #ccc; padding:5px;">Grupa</th>
                        <th style="border:1px solid #ccc; padding:5px;">Cijena po polazniku (€)</th>
                        <th style="border:1px solid #ccc; padding:5px;">Broj polaznika</th>
                        <th style="border:1px solid #ccc; padding:5px;">Ukupna zarada (€)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($zaradaGrupe as $g): ?>
                        <tr>
                            <td style="border:1px solid #ccc; padding:5px;">
                                <a href="<?= __SITE_URL ?>/index.php?rt=aktivnosti/grupa&id=<?= $g['id_grupe'] ?>&from=postavke">
                                    <?php echo htmlspecialchars($g['ime_grupe']); ?>
                                </a>
                            </td>
                            <td style="border:1px solid #ccc; padding:5px;"><?php echo number_format($g['cijena'], 2); ?></td>
                            <td style="border:1px solid #ccc; padding:5px;"><?php echo $g['broj_polaznika']; ?></td>
                            <td style="border:1px solid #ccc; padding:5px;">
                                <?php echo number_format($g['cijena'] * $g['broj_polaznika'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
            </table>
        <?php else: ?>
    <p>Nema grupa.</p>
<?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($_SESSION['tip_korisnika'] === 'roditelj' || $_SESSION['tip_korisnika'] === 'trener'): ?>

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
