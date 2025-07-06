<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<?php if (count($obavijestiList) == 0): ?>
    <p>Nema novih obavijesti.</p>
<?php else: ?>
    <?php 
        foreach( $obavijestiList as $obavijest )
        {
            echo '<div class="obavijest">
                    <div class="obavijest-header">
                        <span class="obavijest-naziv">' . $obavijest->ime . '</span>
                        <span class="obavijest-vrijeme">' . $obavijest->datum . ' ' . $obavijest->vrijeme . '</span>
                    </div>
                    <div class="obavijest-tekst">' . $obavijest->comment . '</div>
                </div>';
        
        }
    ?>
<?php endif; ?>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>