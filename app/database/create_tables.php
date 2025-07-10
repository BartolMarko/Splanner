<?php

require_once __DIR__ . '/db.class.php';

create_table_korisnici();
create_table_grupe();
create_table_pripadnost();
create_table_redovni_termini();
create_table_azurni_termini();
create_table_obavijesti();
create_table_aktivnosti();

exit( 0 );

// --------------------------

function has_table( $tblname )
{
	$db = DB::getConnection();
	
	try
	{
		$st = $db->prepare( 
			'SHOW TABLES LIKE :tblname'
		);

		$st->execute( array( 'tblname' => $tblname ) );
		if( $st->rowCount() > 0 )
			return true;
	}
	catch( PDOException $e ) { exit( "PDO error [show tables]: " . $e->getMessage() ); }

	return false;
}


function create_table_korisnici()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_korisnici' ) )
		echo( 'Tablica splanner_korisnici vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare(
			'CREATE TABLE IF NOT EXISTS splanner_korisnici (' .
			'id_korisnici INT NOT NULL PRIMARY KEY AUTO_INCREMENT, ' .
			'OIB CHAR(11) NOT NULL  ,' .
			'username varchar(50) NOT NULL,' .
			'password_hash varchar(255) NOT NULL,'.
			'email varchar(50) NOT NULL,' .
			'tip_korisnika ENUM("trener","dijete","roditelj"), ' .
			'spol varchar(30), ' .
			'datum_rodenja DATE,'.
			'registration_sequence varchar(20) NOT NULL,' .
			'fk_id_roditelja INT,' .
			'prima_obavijest BOOL,' . 
			'has_registered int)'
		);

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_korisnici]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_korisnici.<br />";
}


function create_table_aktivnosti()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_aktivnosti' ) )
		echo( 'Tablica splanner_aktivnosti vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare( 
			'CREATE TABLE IF NOT EXISTS splanner_aktivnosti (' .
			'id_aktivnosti INT NOT NULL PRIMARY KEY AUTO_INCREMENT, ' .
			'ime varchar(100),' .
			'description varchar(1000) NOT NULL,' .
			'fk_id_trenera INT NOT NULL, ' .
			'grad varchar(50))'
		);

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_aktivnosti]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_aktivnosti.<br />";
}



function create_table_grupe()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_grupe' ) )
		echo( 'Tablica splanner_grupe vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare(  //mozda dodati max broj ljudi u grupi
			'CREATE TABLE IF NOT EXISTS splanner_grupe (' .
			'id_grupe INT NOT NULL PRIMARY KEY AUTO_INCREMENT, ' .
			'ime varchar(100), ' .
			'cijena DECIMAL(15,2), ' .
			'spol varchar(30), ' .
			'uzrast_od INT,' .
			'uzrast_do INT,' . 
			'fk_id_aktivnosti INT NOT NULL)'
		);

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_grupe]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_grupe.<br />";
}

function create_table_pripadnost()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_pripadnost' ) )
		echo( 'Tablica splanner_pripadnost vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare( 
			'CREATE TABLE IF NOT EXISTS splanner_pripadnost (' .
			'id_veze INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
			'id_grupe_fk INT NOT NULL,' .
            'id_korisnik_fk INT NOT NULL )'
		);
		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_pripadnost]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_pripadnost.<br />";
}

function create_table_redovni_termini()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_redovni_termini' ) )
		echo( 'Tablica splanner_redovni_termini vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare(
			'CREATE TABLE IF NOT EXISTS splanner_redovni_termini (' .
			'id_redovni_termini INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
			'id_grupe_fk INT NOT NULL ,' . 
			'id_trener_fk INT NOT NULL ,' . 
			'dan ENUM("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"), ' .
			'vrijeme_poc TIME,' .
			'vrijeme_kraj TIME,' .
			'dvorana varchar(50))'
		);
		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_redovni_termini]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_redovni_termini.<br />";
}

function create_table_azurni_termini()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_azurni_termini' ) )
		echo( 'Tablica splanner_azurni_termini vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare(
			'CREATE TABLE IF NOT EXISTS splanner_azurni_termini (' .
			'id_azurni_termini INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
			'fk_id_redovni_termini INT NOT NULL ,' .
			'id_grupe_fk INT NOT NULL ,' . 
			'id_trener_fk INT NOT NULL ,' . 
			'datum_origin DATE,' .
			'datum_novi DATE,' .
			'vrijeme_poc_stari TIME,' .
			'vrijeme_kraj_stari TIME,' .
			'vrijeme_poc_novi TIME,' .
			'vrijeme_kraj_novi TIME,' .
			'dvorana varchar(50))'
		);
		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_termini]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_termini.<br />";
}



function create_table_obavijesti()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_obavijesti' ) )
		echo( 'Tablica splanner_obavijesti vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare( 
			'CREATE TABLE IF NOT EXISTS splanner_obavijesti (' .
			'id_obavijest INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
			'id_grupe_fk INT NOT NULL ,' .
			'datum DATE,' .
			'vrijeme TIME,' .
			'comment varchar(1000))'
		);
		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_obavijesti]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_obavijesti.<br />";
}

?>
