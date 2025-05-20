<?php

require_once __DIR__ . '/db.class.php';

create_table_korisnici();
create_table_grupe();
create_table_pripadnost();
create_table_termini();
create_table_obavijesti();
alter_table_grupe();


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
			'registration_sequence varchar(20) NOT NULL,' .
			'has_registered int)'
		);

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_korisnici]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_korisnici.<br />";
}


function create_table_grupe()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_grupe' ) )
		echo( 'Tablica splanner_grupe vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare( 
			'CREATE TABLE IF NOT EXISTS splanner_grupe (' .
			'id_grupe INT NOT NULL PRIMARY KEY AUTO_INCREMENT, ' .
			'description varchar(1000) NOT NULL,' .
            'cijena decimal(15,2) NOT NULL)'
		);

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_grupe]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_grupe.<br />";
}

function create_table_pripadnost()
{
	$db = DB::getConnection();

	if( has_table( 'veza_je_u' ) )
		echo( 'Tablica veza_je_u vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare( 
			'CREATE TABLE IF NOT EXISTS veza_je_u (' .
			'id_veze INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
			'id_grupe_fk INT NOT NULL,' .
            'id_korisnik_fk INT NOT NULL, ' .
			'description varchar(1000) NOT NULL,' .
            'cijena decimal(15,2) NOT NULL)');
			//'FOREIGN KEY(id_korisnik_fk) REFERENCES splanner_korisnici(id_korisnici),' .
			//'FOREIGN KEY(id_grupe_fk) REFERENCES splanner_grupe(id_grupe))'

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create veza_je_u]: " . $e->getMessage() ); }

	echo "Napravio tablicu veza_je_u.<br />";
}

function create_table_termini()
{
	$db = DB::getConnection();

	if( has_table( 'splanner_termini' ) )
		echo( 'Tablica splanner_termini vec postoji. Obrisite ju pa probajte ponovno.' );

	try
	{
		$st = $db->prepare( 
			'CREATE TABLE IF NOT EXISTS splanner_termini (' .
			'id_termini INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
			'id_grupe_fk INT NOT NULL ,' . //???
			'id_trener_fk INT NOT NULL ,' .
			'datum DATE,' .
			'vrijeme_poc TIME,' .
			'vrijeme_traj INT,' .
			'dvorana varchar(50),' .
			'comment varchar(1000))');
			//'FOREIGN KEY(id_trener_fk) REFERENCES splanner_korisnici(id_korisnici),' .
			//'FOREIGN KEY(id_grupe_fk) REFERENCES splanner_grupe(id_grupe))'

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
			'comment varchar(1000))');
			//'FOREIGN KEY(id_trener_fk) REFERENCES splanner_korisnici(id_korisnici),' .
			//'FOREIGN KEY(id_grupe_fk) REFERENCES splanner_grupe(id_grupe))'

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error [create splanner_obavijesti]: " . $e->getMessage() ); }

	echo "Napravio tablicu splanner_obavijesti.<br />";
}

function alter_table_grupe(){
    $db = DB::getConnection();
    if( !has_table( 'splanner_grupe' ) )
		echo( 'Tablica splanner_grupe ne postoji.' );

        try
        {

			/*$st = $db->prepare( 
                'ALTER TABLE splanner_grupe ADD CONSTRAINT FOREIGN KEY(id_termin_fk) REFERENCES splanner_termini(id_termini)' 
            );

            $st->execute();*/
        }
        catch( PDOException $e ) { exit( "PDO error [create ALTER_GRUPE]: " . $e->getMessage() ); }
}


?>
