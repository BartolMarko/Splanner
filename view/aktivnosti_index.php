<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<h2>Moje aktivnosti</h2>

<?php if ($tip === 'roditelj'): ?>
    <label for="dijete_select">Prikaži aktivnosti za:</label>
    <select id="dijete_select">
        <option value="self">Sebe</option>
        <?php foreach ($djeca as $d): ?>
            <option value="<?= $d['id_korisnici'] ?>"><?= htmlspecialchars($d['username']) ?></option>
        <?php endforeach; ?>
    </select>
<?php endif; ?>

<div id="aktivnosti_container">
    <?php foreach ($aktivnosti as $a): ?>
        <div class="aktivnost" data-aktivnost-id="<?= $a['id_aktivnosti'] ?>">
            <h3><?= htmlspecialchars($a['ime']) ?></h3>
            <p><?= htmlspecialchars($a['description']) ?></p>
            <p>Cijena: <?= htmlspecialchars($a['cijena']) ?> EUR</p>

            <?php if ($tip === 'trener'): ?>
                <button class="uredi-btn" data-id="<?= $a['id_aktivnosti'] ?>">Uredi aktivnost</button>
                <button class="toggle-grupe-btn" data-id="<?= $a['id_aktivnosti'] ?>">➤ Prikaži grupe</button>
                <div class="grupe" id="grupe_<?= $a['id_aktivnosti'] ?>" style="display:none;">
                    <!-- tu cu grupe ucitati ajaxom i ispisati -->
                </div>
            <?php elseif ($tip === 'roditelj'): ?>
                <button class="ispisi-btn" data-id="<?= $a['id_aktivnosti'] ?>">Ispiši se</button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>


<!-- tu mi je javascript za sve -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // roditelj odabrao dijete (ili sebe)
    $('#dijete_select').on('change', function() {
        let dijeteId = $(this).val();
        getAktivnostiDjeteta(dijeteId);
    });

    // trener zeli urediti aktivnost
    $('.uredi-btn').on('click', function() {
        let aktivnostId = $(this).data('id');
        urediAktivnost(aktivnostId);
    });

    // trener zeli da prikazem (ili maknem prikaz) svih grupa za danu aktivnost
    $('.toggle-grupe-btn').on('click', function() {
        let aktivnostId = $(this).data('id');
        toggleGrupe(aktivnostId);
    });

    // roditelj pritisnuo gummb za ispisati se
    $('.ispisi-btn').on('click', function() {
        let aktivnostId = $(this).data('id');
        ispisiSe(aktivnostId);
    });

    
    function getAktivnostiDjeteta(dijeteId) {
        console.log("Dohvacam aktivnosti djeteta ID:", childId);
        // ajax za reloadanje containera za aktivnosti
        $.ajax({
            url: 'ajax/aktivnosti_ajax.php',
            method: 'POST',
            data: {
                action: 'get_aktivnosti',
                user_id: dijeteId
            },
            success: function(dobiveniPod) {
                if (dobiveniPod.error) alert(dobiveniPod.error);
                else $('#aktivnosti_container').html(dobiveniPod.html);
            },
            error: function() {
                alert('Greška pri dohvaćanju aktivnosti.');
            }
        });
    }

    function urediAktivnost(aktivnostId) {
        console.log("Uredujem aktivnost:", aktivnostId);
        // 
    }

    function toggleGrupe(aktivnostId) {
        console.log("Togglam grupe za aktivnost:", aktivnostId);
        // ili obrisem ili ucitam ajax-om sve grupe
        //gledat cu jel prvi ili ne-prvi put kliknut
        const grupeDiv = $('#grupe_' + aktivnostId);
        if (grupeDiv.is(':visible')) {
            grupeDiv.slideUp();
        } else {
            $.ajax({
                url: 'ajax/aktivnosti_ajax.php',
                method: 'POST',
                data: {
                    action: 'get_grupe',
                    aktivnost_id: aktivnostId
                },
                success: function(dobiveniPod) {
                    container.html(dobiveniPod).slideDown();
                },
                error: function() {
                    alert('Greška pri dohvaćanju grupa.');
                }
            });
        }
    }

    function ispisiSe(aktivnostId) {
        console.log("Ispis sa:", aktivnostId);
        // ajax kojim izbrisem iz tablica
        if (!confirm("Jeste li sigurni da se želite ispisati s ove aktivnosti?")) return;

        $.ajax({
            url: 'ajax/aktivnosti_ajax.php',
            method: 'POST',
            data: {
                action: 'ispisi_se',
                aktivnost_id: aktivnostId,
                child_id: $('#dijete_select').val() || 'self'
            },
            success: function(dobiveniPod) {
                getAktivnostiDjeteta($('#dijete_select').val() || 'self'); //ili svoje aktivnosti ili odabranog djeteta
            },
            error: function() {
                alert('Greška pri ispisu.');
            }
        });
    }

});
</script>
