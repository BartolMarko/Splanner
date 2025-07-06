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
<?php if ($tip === 'trener'): ?>
    <button id="nova-aktivnost-btn">&#x2795; Stvori novu aktivnost</button>
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
    //TODO!!!!!: DODATI GUMB TRENERU ZA STVARANJE NOVE AKTIVNOSTI
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

    $('#nova-aktivnost-btn').on('click', function () {
    const formaHtml = `
        <div class="aktivnost-form nova-aktivnost-form">
            <input type="text" placeholder="Ime aktivnosti" class="ime" required>
            <textarea placeholder="Opis" class="opis"></textarea>
            <input type="number" placeholder="Cijena u EUR" step="0.01" class="cijena">
            <button class="spremi-novu-btn">✅ Spremi</button>
            <button class="odustani-btn">❌ Odustani</button>
        </div>
    `;
    $('#aktivnosti_container').prepend(formaHtml);
    });

    $(document).on('click', '.odustani-btn', function () { //odustao
    $(this).closest('.nova-aktivnost-form').remove();
    });

    $(document).on('click', '.spremi-novu-btn', function () { //trener sprema novu aktivnost
        const $form = $(this).closest('.nova-aktivnost-form');

        const ime = $form.find('.ime').val().trim();
        const opis = $form.find('.opis').val().trim();
        const cijena = $form.find('.cijena').val().trim();

        if (!ime || !opis || !cijena) {
            alert("Sva polja su obavezna.");
            return;
        }

        $.ajax({
            url: 'ajax/aktivnosti_ajax.php',
            method: 'POST',
            data: {
                action: 'create_aktivnost',
                ime: ime,
                description: opis,
                cijena: cijena
            },
            success: function (response) {
                if (response.success) {
                    alert("Aktivnost je uspješno dodana!");
                    location.reload(); //ponovno ucitam stranicu
                } else if (response.error) {
                    alert("Greška: " + response.error);
                }
            },
            error: function () {
                alert("Došlo je do greške pri slanju zahtjeva.");
            }
        });
    });

    $(document).on('click', '.spremi-termin-btn', function () {
    const $grupaDiv = $(this).closest('.grupa');
    const datum = $grupaDiv.find('.datum').val();
    const vrijemePoc = $grupaDiv.find('.vrijeme_poc').val();
    const vrijemeKraj = $grupaDiv.find('.vrijeme_kraj').val();
    const dvorana = $grupaDiv.find('.dvorana').val();
    const izvanredan = $grupaDiv.find('.izvanredan-check').is(':checked') ? 1 : 0;
    const idGrupe = $grupaDiv.data('grupa-id');

    if (!datum || !vrijemePoc || !vrijemeKraj || !dvorana) {
    alert("Sva polja su obavezna.");
    return;
    }

        $.ajax({
            url: 'ajax/aktivnosti_ajax.php',
            method: 'POST',
            data: {
                action: 'update_termin',
                grupa_id: idGrupe,
                datum: datum,
                vrijeme_poc: vrijemePoc,
                vrijeme_kraj: vrijemeKraj,
                dvorana: dvorana,
                izvanredan: izvanredan
            },
            success: function (response) {
                if (response.success) {
                    $grupaDiv.html(`<strong class="ime">${response.ime}</strong> - <span class="termin">${response.termin}</span> <button class="uredi-termin-btn">✏️ Uredi termin</button>`);
                } else {
                    alert(response.error || "Greška.");
                }
            },
            error: function () {
                alert("Greška pri spremanju termina.");
            }
        });
    });

$(document).on('click', '.odustani-termin-btn', function () {
    const $grupaDiv = $(this).closest('.grupa');
    const originalHtml = $grupaDiv.data('originalHtml');
    $grupaDiv.html(originalHtml);
});


$(document).on('click', '.spremi-btn', function () { //trener sprema promjene na aktivnosti
    const form = $(this).closest('.aktivnost-form');
    const id = $(this).data('id');
    const ime = form.find('.ime').val();
    const opis = form.find('.opis').val();
    const cijena = form.find('.cijena').val();

    $.ajax({
        url: 'ajax/aktivnosti_ajax.php',
        method: 'POST',
        data: {
            action: 'update_aktivnost',
            id: id,
            ime: ime,
            description: opis,
            cijena: cijena
        },
        success: function () {
            location.reload();
        },
        error: function () {
            alert('Greška pri ažuriranju.');
        }
    });
});

$(document).on('click', '.uredi-termin-btn', function () {
    const $grupaDiv = $(this).closest('.grupa');
    const terminText = $grupaDiv.find('.termin').text();

    const formHtml = `
    <label>Datum: <input type="date" class="datum"></label><br>
    <label>Vrijeme početka: <input type="time" class="vrijeme_poc"></label><br>
    <label>Trajanje (u min): <input type="number" class="vrijeme_kraj" min="1"></label><br>
    <label>Dvorana: <input type="text" class="dvorana"></label><br>
    <label><input type="checkbox" class="izvanredan-check"> Izvanredan</label><br>
    <button class="spremi-termin-btn">💾 Spremi</button>
    <button class="odustani-termin-btn">❌ Odustani</button>
`;

    $grupaDiv.data('originalHtml', $grupaDiv.html()); // spremam stari prikaz
    $grupaDiv.html(formHtml);
});

$(document).on('click', '.obrisi-btn', function () { //trener brise aktivnost
    const id = $(this).data('id');
    if (!confirm("Jeste li sigurni da želite obrisati ovu aktivnost?")) return;

    $.ajax({
        url: 'ajax/aktivnosti_ajax.php',
        method: 'POST',
        data: {
            action: 'delete_aktivnost',
            id: id
        },
        success: function () {
            location.reload();
        },
        error: function () {
            alert('Greška pri brisanju.');
        }
    });
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
        const aktivnostDiv = $(`.aktivnost[data-aktivnost-id="${aktivnostId}"]`); //nadem div aktivnosti koju sam odabrao
        const ime = aktivnostDiv.find('.naziv').text();
        const opis = aktivnostDiv.find('.opis').text();
        const cijenaText = aktivnostDiv.find('.cijena').text().replace(/\D/g, ''); //izbrise sve znakove koji nisu brojevi u polju za cijenu
        
        const formaHtml = `
            <div class="aktivnost-form">
                <input type="text" class="ime" value="${ime}" required>
                <textarea class="opis">${opis}</textarea>
                <input type="number" class="cijena" step="0.01" value="${cijenaText}">
                <button class="spremi-btn" data-id="${aktivnostId}">💾 Spremi</button>
                <button class="odustani-btn">❌ Odustani</button>
                <button class="obrisi-btn" data-id="${aktivnostId}" style="color:red;">🗑️ Obriši</button>
            </div>
        `;

    aktivnostDiv.html(formaHtml); 
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
