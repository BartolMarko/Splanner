
<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<?php if ($_SESSION['tip_korisnika'] === 'roditelj'): ?>
<div id="filter-container">
    <p>Filtriraj obavijesti po korisniku:</p>
    <?php foreach ($imenaKorisnika as $id => $username): ?>
        <label class="korisnik-switch">
            <input type="checkbox" class="korisnik_checkbox" value="<?= $id ?>" checked>
            <span class="switch"></span>
            <span class="username"><?= htmlspecialchars($username) ?></span>
        </label>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div id="obavijesti-container">
    <?php require __SITE_PATH . '/view/obavijesti_filter.php'; ?>
</div>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.js"></script>
<script>
    $(function() {
        $('.korisnik_checkbox').change(function() {
            const selected = $('.korisnik_checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            //console.log(selected);

            $.post('index.php?rt=obavijesti/filter', { ids: selected }, function(html) {
                $('#obavijesti-container').html(html);
            }).fail(function() {
                alert('Greška pri učitavanju obavijesti.');
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.korisnik_checkbox');

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const checked = Array.from(checkboxes).filter(c => c.checked);
                if (checked.length === 0) {
                    this.checked = true;
                }
            });
        });
    });
</script>
