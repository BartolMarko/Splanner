<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<h3>Rezultati pretrage:</h3>

<?php if (!empty($rezultati)): ?>
	<?php foreach ($rezultati as $rez): ?>
		<div class="rezpret kosarica">
			<strong><?= htmlspecialchars($rez['ime']) ?></strong><br>
			Cijena: <?= htmlspecialchars($rez['cijena']) ?> EUR<br>
			Grad: <?= htmlspecialchars($rez['grad']) ?>
			<br>
			<a href="<?= __SITE_URL ?>/index.php?rt=aktivnosti/grupa&id=<?= htmlspecialchars($rez['id_grupe']) ?>&from=pretraga">Detalji</a> <!-- promjena -->
		</div>
	<?php endforeach; ?>
<?php else: ?>
	<p>Nema rezultata koji odgovaraju pretrazi.</p>
<?php endif; ?>

<a href="<?= __SITE_URL ?>/index.php?rt=pretraga">↩ Natrag</a>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>