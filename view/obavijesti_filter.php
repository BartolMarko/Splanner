
<?php if (count($obavijestiList) == 0): ?>
    <p>Nema novih obavijesti.</p>
<?php else: ?>
    <?php 
        foreach( $obavijestiList as $obavijest )
        {
            echo '<div class="obavijest" onclick="window.location.href=\'index.php?rt=aktivnosti/grupa&id='. $obavijest->id_grupe_fk .'\'">
                    <div class="obavijest-header">
                        <span class="obavijest-naziv">' . $obavijest->aktivnost_ime . ', ' . $obavijest->ime . '</span>
                        <span class="obavijest-vrijeme">' . $obavijest->datum . ' ' . $obavijest->vrijeme . '</span>
                    </div>
                    <div class="obavijest-tekst">' . $obavijest->comment . '</div>
                </div>';
        
        }
    ?>
<?php endif; ?>

