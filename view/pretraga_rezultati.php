<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<h3>Rezultati pretrage:</h3>

<?php if (!empty($rezultati)): ?>
	<ul>
		<?php foreach ($rezultati as $rez): ?>
			<li>
				<b><?php echo htmlspecialchars($rez[0]); ?></b> —
				<?php echo htmlspecialchars($rez[1]); ?> EUR<br>
				<i><?php echo htmlspecialchars($rez[2]); ?></i>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else: ?>
	<p>Nema rezultata.</p>
<?php endif; ?>

<a href="<?php echo __SITE_URL; ?>/index.php?rt=pretraga">Natrag na pretragu</a>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>