<!DOCTYPE html>
<html>
<head>
	<meta charset="utf8">
	<title>Splanner</title>
	<link rel="stylesheet" href="<?php echo __SITE_URL;?>/css/style.css">
	<link rel="icon" type="image/webp" href="<?= __SITE_URL ?>/css/sticker.webp">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.js"></script> 
</head>
<body <?php if (isset($_SESSION['username'])) echo ' class="with-nav"'; ?>>

	<?php if (isset($_SESSION['username'])): ?>
	<nav>
		<ul>
			<li>
				  <img src="<?= __SITE_URL ?>/css/sticker.webp" alt="Logo" style="height:100px;">
			</li>
			<li><a href="<?php echo __SITE_URL; ?>/index.php?rt=raspored">Raspored</a></li>
			<?php if ($_SESSION['tip_korisnika'] !== 'trener'): ?>
				<li><a href="<?php echo __SITE_URL; ?>/index.php?rt=obavijesti">Obavijesti</a></li>
			<?php endif; ?>
			<li><a href="<?php echo __SITE_URL; ?>/index.php?rt=aktivnosti">Aktivnosti</a></li>
			<li><a href="<?php echo __SITE_URL; ?>/index.php?rt=postavke">Postavke</a></li>
			<li>
				<a href="<?php echo __SITE_URL; ?>/index.php?rt=login/logout"
				onclick="return confirm('Jeste li sigurni da se želite odjaviti?');">
				Logout
				</a>
			</li>
		</ul>
	</nav>
	<?php endif; ?>

	<main>
		<h1><?php echo $title; ?></h1>
		
		
