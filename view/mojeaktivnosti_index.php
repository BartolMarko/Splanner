<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<!-- <h2>Moje aktivnosti</h2> -->

<?php if ($tip === 'roditelj'): ?>
    <label for="dijete_select">Prikaz aktivnosti po članovima obitelji:</label>
    <select id="dijete_select">
        <option value="<?= $_SESSION['id_user'] ?>" selected>Moje aktivnosti</option>
        <?php foreach ($djeca as $d): ?>
            <option value="<?= $d['id_korisnici'] ?>"><?= htmlspecialchars($d['username']) ?></option>
        <?php endforeach; ?>
    </select>
<?php endif; ?>
<?php if ($tip === 'trener'): ?>
    <button id="nova-aktivnost-btn">&#x2795; Stvori novu aktivnost</button>
<?php endif; ?>
<div id="aktivnosti_container">
    <?php $i=0;?>
    <?php foreach ($aktivnosti as $a): ?>
        <div class="aktivnost kosarica" 
        data-aktivnost-id="<?php echo ($tip === 'trener') ? $a['id_aktivnosti'] : $a['id_grupe']; ?>" 
        <?php if ($tip === 'dijete'): ?>
            onclick="window.location.href='index.php?rt=aktivnosti/grupa&id=<?php echo $a['id_grupe']; ?>'"
        <?php endif; ?>>
        <?php if ($tip === 'trener'): ?>
            <h3><?= htmlspecialchars($a['ime']) ?></h3>
            <p><?= htmlspecialchars($a['description']) ?></p>
            <p>Grad: <?= htmlspecialchars($a['grad']) ?></p>
            <br>
            <button class="uredi-btn" data-id="<?= $a['id_aktivnosti'] ?>">Uredi aktivnost</button>
            <button class="toggle-grupe-btn" data-id="<?= $a['id_aktivnosti'] ?>">➤ Prikaži grupe</button>
            <button class="dodaj-grupu-btn" data-aktivnost-id="<?= $a['id_aktivnosti'] ?>">➕ Nova grupa</button>
            <div class="grupe" id="grupe_<?= $a['id_aktivnosti'] ?>" style="display:none;">
                <!-- tu cu grupe ucitati ajaxom i ispisati -->
            </div>
            <?php else: ?>
                <h3 onclick="window.location.href='index.php?rt=aktivnosti/grupa&id=<?php echo $a['id_grupe']; ?>'"><?php echo htmlspecialchars($detalji_akt[$i]['ime']) .': '. htmlspecialchars($a['ime']); ?></h3>
                <p><?= htmlspecialchars($detalji_akt[$i]['description']) ?></p>
                <br>
                <p>Grad: <?= htmlspecialchars($detalji_akt[$i]['grad']) ?></p>
                <p>Cijena: <?= htmlspecialchars($a['cijena']) ?></p>
                <p>Dob članova: <?= htmlspecialchars($a['uzrast_od']) ?> - <?= htmlspecialchars($a['uzrast_do']) ?></p>
                <?php if ($tip === 'roditelj'): ?>
                <button class="ispisi-btn" data-id="<?= $a['id_grupe'] ?>">Ispis</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php ++$i; ?>
    <?php endforeach; ?>
</div>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>


<!-- tu mi je javascript za sve -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
    let jel_otvorena_grupa=false;
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
            <input type="text" placeholder="Grad" class="grad" required><br>
            <button class="spremi-novu-btn">✅ Spremi</button>
            <button class="odustani-btn">❌ Odustani</button>
        </div>
    `;
    $('#aktivnosti_container').prepend(formaHtml);
    });

    $(document).on('click', '.odustani-btn', function () { //odustao
    $(this).closest('.nova-aktivnost-form').remove();
    });


    $(document).on('click', '.odustani-btn-uredi', function () { //odustao
    $(this).closest('.aktivnost-form').remove();
    location.reload();
    });

    $(document).on('click', '.dodaj-grupu-btn', function () {
    if(jel_otvorena_grupa) return;
    const aktivnostId = $(this).data('aktivnost-id');
    const $grupeDiv = $('#grupe_' + aktivnostId);
    jel_otvorena_grupa=true;
    const novaGrupaHtml = `
        <div class="grupa nova-grupa-form">
            <label>Ime grupe: <br><input type="text" class="ime-grupe" required></label><br>
            <label>Spol grupe:<br>
            <select class="spol" name="spol" required>
                <option value="" disabled selected>Odaberi</option>
                <option value="oboje">Mješovito</option>
                <option value="žensko">Ženski</option>
                <option value="muško">Muški</option>
            </select>
            </label><br>
            <label>Minimalna dob: <br><input type="number" class="dob-min"></label><br>
            <label>Maksimalna dob: <br><input type="number" class="dob-max"></label><br>
            Cijena:<br><input type="number" class="cijena" step="0.01"><br>
            <button class="spremi-grupu-btn" data-aktivnost-id="${aktivnostId}">💾 Spremi</button>
            <button class="odustani-grupa-btn">❌ Odustani</button>
        </div>
    `;

    $grupeDiv.prepend(novaGrupaHtml).slideDown();
    });


    $(document).on('click', '.odustani-grupa-btn', function () {
    jel_otvorena_grupa=false;
    $(this).closest('.nova-grupa-form').remove();
});

$(document).on('click', '.spremi-grupu-btn', function () {
    jel_otvorena_grupa=false;
    const $form = $(this).closest('.nova-grupa-form');
    const aktivnostId = $(this).data('aktivnost-id');
    const imeGrupe = $form.find('.ime-grupe').val().trim();
    const cijena = $form.find('.cijena').val().trim();
    const dobMin= $form.find('.dob-min').val().trim();
    const dobMax = $form.find('.dob-max').val().trim();
    const spol = $form.find('.spol').val().trim();

    if (!imeGrupe) {
        alert("Ime grupe je obavezno.");
        return;
    }

    $.ajax({
        url: 'ajax/aktivnosti_ajax.php',
        method: 'POST',
        data: {
            action: 'create_grupa',
            aktivnost_id: aktivnostId,
            ime: imeGrupe,
            cijena: cijena,
            dobMin: dobMin,
            dobMax: dobMax,
            spol:spol
        },
        success: function (response) {
            if (response.success) {
                alert("Grupa dodana.");
                $form.remove();
                toggleGrupe(aktivnostId); // osvježi grupe
            } else {
                alert("Greška: " + (response.error || "Nepoznata greška."));
            }
        },
        error: function () {
            alert("Greška pri dodavanju grupe.");
        }
    });
});





    $(document).on('click', '.spremi-novu-btn', function () { //trener sprema novu aktivnost
        const $form = $(this).closest('.nova-aktivnost-form');

        const ime = $form.find('.ime').val().trim();
        const opis = $form.find('.opis').val().trim();
        const grad = $form.find('.grad').val().trim();

        if (!ime || !opis || !grad) {
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
                grad: grad
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
    const idGrupe = $grupaDiv.data('grupa-id');
    const $terminDiv = $(this).closest('.termin');
    const izvanredan = ($terminDiv.find('.izvanredan-check-term').is(':checked') ? ('izvanredan') : ('redovan'));
    const idTerminRed=$terminDiv.attr('valuered');
    const idTerminAzur=$terminDiv.attr('valueazur');
    const aktivnostId = $grupaDiv.closest('.aktivnost').data('aktivnost-id');
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
                tip_termina: izvanredan,
                id_red: idTerminRed,
                id_azur: idTerminAzur
            },
            success: function (response) {
                if (response.success) {
                    //$terminDiv.html(`<strong class="ime">${response.ime}</strong> - <span class="termin">${response.termin}</span> <button class="uredi-termin-btn">✏️ Uredi termin</button>`);
                    toggleGrupe(aktivnostId);
                } else {
                    alert(response.error || "Greška.");
                }
            },
            error: function () {
                alert("Greška pri spremanju termina.");
            }
        });
    });

    $(document).on('click', '.spremi-novi-termin-btn', function () {
    const $grupaDiv = $(this).closest('.grupa');
    const datum = $grupaDiv.find('.datum').val();
    const vrijemePoc = $grupaDiv.find('.vrijeme_poc').val();
    const vrijemeKraj = $grupaDiv.find('.vrijeme_kraj').val();
    const dvorana = $grupaDiv.find('.dvorana').val();
    const izvanredan = ($grupaDiv.find('.izvanredan-check-grp').is(':checked') ? ('izvanredan') : ('redovan'));
    const idGrupe = $grupaDiv.data('grupa-id');
    const idTrener = <?= json_encode($_SESSION['id_user']) ?>;

    if (!datum || !vrijemePoc || !vrijemeKraj || !dvorana) {
        alert("Sva polja su obavezna.");
        return;
    }

    $.ajax({
        url: 'ajax/aktivnosti_ajax.php',
        method: 'POST',
        data: {
            action: 'create_termin',
            tip_termina: izvanredan,
            id_grupe: idGrupe,
            id_trener: idTrener,
            datum: datum,
            vrijeme_poc: vrijemePoc,
            vrijeme_kraj: vrijemeKraj,
            dvorana: dvorana
        },
        success: function (response) {
            if (response.success) {
                alert("Termin dodan.");
                toggleGrupe($grupaDiv.closest('.aktivnost').data('aktivnost-id')); // osvježi prikaz
            } else {
                alert("Greška: " + (response.error || "Nepoznata greška."));
            }
        },
        error: function () {
            alert("Greška pri dodavanju termina.");
        }
    });
});


$(document).on('click', '.odustani-termin-btn-grp', function () {
    const $grupaDiv = $(this).closest('.grupa');
    const originalHtml = $grupaDiv.data('originalHtml');

    if (originalHtml) {
        $grupaDiv.html(originalHtml);
        console.log('Restored original HTML');
    } else {
        console.warn('No original HTML found on .grupa');
        console.log('Closest .grupa:', $grupaDiv[0]);
        console.log('Current .grupa HTML:', $grupaDiv.html());
    }
});

$(document).on('click', '.odustani-termin-btn-term', function () {
    const $grupaDiv = $(this).closest('.termin');
    const originalHtml = $grupaDiv.data('originalHtml');

    if (originalHtml) {
        $grupaDiv.html(originalHtml);
        console.log('Restored original HTML');
    } else {
        console.warn('No original HTML found on .grupa');
        console.log('Closest .grupa:', $grupaDiv[0]);
        console.log('Current .grupa HTML:', $grupaDiv.html());
    }
});



$(document).on('click', '.spremi-btn', function () { //trener sprema promjene na aktivnosti
    const form = $(this).closest('.aktivnost-form');
    const id = $(this).data('id');
    const ime = form.find('.ime').val();
    const opis = form.find('.opis').val();
    const grad= form.find('.grad').val().trim();

    if(grad==='' || ime==='' || opis===''){
        alert('Unos u sva polja je obavezan.');
        return;
    }

    $.ajax({
        url: 'ajax/aktivnosti_ajax.php',
        method: 'POST',
        data: {
            action: 'update_aktivnost',
            id: id,
            ime: ime,
            description: opis,
            grad:grad,
        },
        success: function () {
            location.reload();
        },
        error: function () {
            alert('Greška pri ažuriranju.');
        }
    });
});

$(document).on('click', '.izvanredan-check-term', function () {
    const $label = $(this).closest('.termin').find('.datum-label');

    const isChecked = $(this).is(':checked');

    if (isChecked) {
        // IZBORT DATUMA
        $label.html('Datum: <input type="date" class="datum">');
    } else {
        // IZBOR DANA  U TJ
        $label.html(`Dan u tjednu: 
            <select class="datum">
                <option value="" disabled selected>Odaberi</option>
                <option value="Monday">Ponedjeljak</option>
                <option value="Tuesday">Utorak</option>
                <option value="Wednesday">Srijeda</option>
                <option value="Thursday">Četvrtak</option>
                <option value="Friday">Petak</option>
                <option value="Saturday">Subota</option>
                <option value="Sunday">Nedjelja</option>
            </select>`);
    }
});


$(document).on('click', '.izvanredan-check-grp', function () {
    const $label = $(this).closest('.grupa').find('.datum-label');

    const isChecked = $(this).is(':checked');

    if (isChecked) {
        // IZBOR DATUMA
        $label.html('Datum: <input type="date" class="datum">');
    } else {
        // IZBOR DANA U TJ
        $label.html(`Dan u tjednu: 
            <select class="datum">
                <option value="" disabled selected>Odaberi</option>
                <option value="Monday">Ponedjeljak</option>
                <option value="Tuesday">Utorak</option>
                <option value="Wednesday">Srijeda</option>
                <option value="Thursday">Četvrtak</option>
                <option value="Friday">Petak</option>
                <option value="Saturday">Subota</option>
                <option value="Sunday">Nedjelja</option>
            </select>`);
    }
});

$(document).on('click', '.objavi-obavijest-btn', function () {
    const $termDiv = $(this).closest('.grupa');
    const idGrupe = $termDiv.data('grupa-id');
    if (!$termDiv.data('originalHtml')) {
        $termDiv.data('originalHtml', $termDiv.html());
    }
    const formHtml = `
        <label for="comment">Tekst obavijesti:</label><br>
        <textarea name="comment" rows="4" required></textarea><br>
        <button type="submit" class="objavi-obavijest-ajax-btn">Objavi obavijest</button>
        <button class="odustani-termin-btn-grp">❌ Odustani</button>
    `;
    $termDiv.html(formHtml);
});

$(document).on('click', '.objavi-obavijest-ajax-btn', function () {
    const $grupaDiv = $(this).closest('.grupa');
    const idGrupe = $grupaDiv.data('grupa-id');
    const comment = $grupaDiv.find('textarea[name="comment"]').val().trim();

    if (!comment) {
        alert("Obavijest ne može biti prazna.");
        return;
    }

    $.ajax({
        url: 'ajax/aktivnosti_ajax.php',
        method: 'POST',
        data: {
            action: 'dodaj_obavijest',
            id_grupe: idGrupe,
            comment: comment
        },
        success: function (response) {
            console.log(response);
            if (response.success) {
                alert("Obavijest uspješno poslana.");
                toggleGrupe($grupaDiv.closest('.aktivnost').data('aktivnost-id')); // TODO
            } else {
                alert("Greška: " + (response.error || "Nepoznata greška."));
            }
        },
        error: function () {
            alert("Greška pri slanju obavijesti.");
        }
    });
});

$(document).on('click', '.dodaj-termin-btn', function () {
    const $termDiv = $(this).closest('.grupa');
    if (!$termDiv.data('originalHtml')) {
    $termDiv.data('originalHtml', $termDiv.html());
    }
    const formHtml = `
    <label class="datum-label">Dan u tjednu: <br><select class="datum">
                <option value="" disabled selected>Odaberi</option>
                <option value="Monday">Ponedjeljak</option>
                <option value="Tuesday">Utorak</option>
                <option value="Wednesday">Srijeda</option>
                <option value="Thursday">Četvrtak</option>
                <option value="Friday">Petak</option>
                <option value="Saturday">Subota</option>
                <option value="Sunday">Nedjelja</option>
            </select></label><br>
    <label>Vrijeme početka: <br><input type="time" class="vrijeme_poc"></label><br>
    <label>Vrijeme završetka: <br><input type="time" class="vrijeme_kraj"></label><br>
    <label>Dvorana: <br><input type="text" class="dvorana"></label><br>
    <label><input type="checkbox" class="izvanredan-check-grp"> Izvanredan</label><br>
    <button class="spremi-novi-termin-btn">💾 Spremi</button>
    <button class="odustani-termin-btn-grp">❌ Odustani</button>
`;

    $termDiv.html(formHtml);
});

$(document).on('click', '.uredi-termin-btn', function () {
    const $grupaDiv = $(this).closest('.termin');
    if (!$grupaDiv.data('originalHtml')) {
    $grupaDiv.data('originalHtml', $grupaDiv.html()); // spremam stari prikaz
    }
    const formHtml = `
    <label class="datum-label">Dan u tjednu: <br><select class="datum">
                <option value="" disabled selected>Odaberi</option>
                <option value="Monday">Ponedjeljak</option>
                <option value="Tuesday">Utorak</option>
                <option value="Wednesday">Srijeda</option>
                <option value="Thursday">Četvrtak</option>
                <option value="Friday">Petak</option>
                <option value="Saturday">Subota</option>
                <option value="Sunday">Nedjelja</option>
            </select></label><br>
    <label>Vrijeme početka: <br><input type="time" class="vrijeme_poc"></label><br>
    <label>Vrijeme završetka: <br><input type="time" class="vrijeme_kraj"></label><br>
    <label>Dvorana: <br><input type="text" class="dvorana"></label><br>
    <label><input type="checkbox" class="izvanredan-check-term"> Izvanredan</label><br>
    <button class="spremi-termin-btn">💾 Spremi</button>
    <button class="odustani-termin-btn-term">❌ Odustani</button>
`;

    
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



$(document).on('click', '.obrisi-termin-btn', function () { //trener brise termin
    const id = $(this).data('id');
    if (!confirm("Jeste li sigurni da želite obrisati ovaj termin?")) return;

    $.ajax({
        url: 'ajax/aktivnosti_ajax.php',
        method: 'POST',
        data: {
            action: 'delete_termin',
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
        
        console.log("Dohvacam grupe djeteta ID:", dijeteId);
    const isSelf = dijeteId === '<?= $_SESSION["id_user"] ?>';
    
    $.ajax({
        url: 'ajax/aktivnosti_ajax.php',
        method: 'POST',
        data: {
            action: 'get_grupe_user',
            user_id: dijeteId
        },
        success: function(response) {
            if (response.success && response.grupe && response.detalji_akt) {
                $('#aktivnosti_container').empty();
                
                
                response.grupe.forEach(function(grupa, index) {
                    const aktivnost = response.detalji_akt[index];
                    
                    const aktivnostHtml = `
                       <div class="aktivnost kosarica" data-aktivnost-id="${grupa.id_grupe}">
                            <h3 style="cursor:pointer;"
                                onclick="window.location.href='index.php?rt=aktivnosti/grupa&id=${grupa.id_grupe}'">
                                ${escapeHtml(aktivnost.ime)}: ${escapeHtml(grupa.ime)}
                            </h3>
                            <p>${escapeHtml(aktivnost.description)}</p><br>
                            <p>Grad: ${escapeHtml(aktivnost.grad)}</p>
                            <p>Cijena: ${escapeHtml(grupa.cijena)}</p>
                            <p>Dob članova: ${escapeHtml(grupa.uzrast_od)} - ${escapeHtml(grupa.uzrast_do)}</p>
                            <button class="ispisi-btn" data-id="${grupa.id_grupe}">Ispis</button>
                        </div>
                    `;
                    $('#aktivnosti_container').append(aktivnostHtml);
                });
                
                $('.ispisi-btn').off('click').on('click', function() {
                    let grupaId = $(this).data('id');
                    ispisiSe(grupaId);
                });
            } else {
                alert(response.error || "Greška pri dohvaćanju aktivnosti");
            }
        },
        error: function() {
            alert('Greška pri dohvaćanju aktivnosti.');
        }
    });
}
//kao htmlspecialchars
function escapeHtml(text) {
    if (text === null || text === undefined) {
        return '';
    }
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}


    function urediAktivnost(aktivnostId) {
        console.log("Uredujem aktivnost:", aktivnostId);
        const aktivnostDiv = $(`.aktivnost[data-aktivnost-id="${aktivnostId}"]`); //nadem div aktivnosti koju sam odabrao
        const ime = aktivnostDiv.find('.naziv').text();
        const opis = aktivnostDiv.find('.opis').text();
        const cijenaText = aktivnostDiv.find('.cijena').text().replace(/\D/g, ''); //izbrise sve znakove koji nisu brojevi u polju za cijenu
        //banana
        const formaHtml = `
            <div class="aktivnost-form">
                <input type="text" class="ime" placeholder="Ime" value="${ime}" required>
                <textarea class="opis" placeholder="Opis" >${opis}</textarea>
                <input type="text" placeholder="Grad" class="grad" required><br>
                <button class="spremi-btn" data-id="${aktivnostId}">💾 Spremi</button> 
                <button class="odustani-btn-uredi">❌ Odustani</button>
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
                    grupeDiv.html(dobiveniPod.html).slideDown();
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
                child_id: $('#dijete_select').val()
            },
            success: function(dobiveniPod) {

                getAktivnostiDjeteta($('#dijete_select').val()); //ili svoje aktivnosti ili odabranog djeteta

            },
            error: function() {
                alert('Greška pri ispisu.');
            }
        });
    }

});
</script>
