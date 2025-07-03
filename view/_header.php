<!DOCTYPE html>
<html>
<head>
	<meta charset="utf8">
	<title>Splanner</title>
	<link rel="stylesheet" href="<?php echo __SITE_URL;?>/css/style.css">
</head>
<body>
	<h1><?php echo $title; ?></h1>

	<?php if (isset($_SESSION['username'])): ?>
	<nav>
		<ul>
			<li><a href="<?php echo __SITE_URL; ?>/index.php?rt=raspored">Raspored</a></li>
			<li><a href="<?php echo __SITE_URL; ?>/index.php?rt=obavijesti">Obavijesti</a></li>
			<li><a href="<?php echo __SITE_URL; ?>/index.php?rt=aktivnosti">Aktivnosti</a></li>
			<li><a href="<?php echo __SITE_URL; ?>/index.php?rt=postavke">Postavke</a></li>
		</ul>
	</nav>
	<?php endif; ?>