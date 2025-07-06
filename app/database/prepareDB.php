<?php

// Stvaramo tablice u bazi (ako već ne postoje od ranije).
require_once __DIR__ . '/db.class.php';

seed_table_korisnici();
seed_table_aktivnosti();
seed_table_grupe();
seed_table_obavijesti();

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


function seed_table_korisnici()
{
	$db = DB::getConnection();

	// Ubaci neke korisnike unutra
	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_korisnici(OIB, username, password_hash, email, tip_korisnika, registration_sequence, has_registered) VALUES (:OIB, :username, :password, \'a@b.com\', :tip, :regseq, :has )' );

		$st->execute( array( 'OIB' => 12345678901, 'username' => 'mirko', 'password' => password_hash( 'mirkovasifra', PASSWORD_DEFAULT ), 'tip' => 'trener', 'regseq' => 'abc123', 'has' => '1' ) );
		$st->execute( array( 'OIB' => 12345678902, 'username' => 'slavko', 'password' => password_hash( 'slavkovasifra', PASSWORD_DEFAULT ), 'tip' => 'dijete', 'regseq' => 'def456', 'has' => '1' ) );
		$st->execute( array( 'OIB' => 12345678903, 'username' => 'ana', 'password' => password_hash( 'aninasifra', PASSWORD_DEFAULT ), 'tip' => 'roditelj', 'regseq' => 'mhffhm78', 'has' => '1' ) );
		$st->execute( array( 'OIB' => 12345678904, 'username' => 'maja', 'password' => password_hash( 'majinasifra', PASSWORD_DEFAULT ), 'tip' => 'dijete', 'regseq' => 'ilzkutj98', 'has' => '1' ) );
		$st->execute( array( 'OIB' => 12345678905, 'username' => 'pero', 'password' => password_hash( 'perinasifra', PASSWORD_DEFAULT ), 'tip' => 'roditelj', 'regseq' => '21354sd', 'has' => '1' ) );
	}
	catch( PDOException $e ) { exit( "PDO error [insert splanner_korisnici]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_korisnici.<br />";
}


// ------------------------------------------
function seed_table_aktivnosti()
{
	$db = DB::getConnection();

	// Ubaci neke proizvode unutra (ovo nije bas pametno ovako raditi, preko hardcodiranih id-eva usera)
	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_aktivnosti(id_aktivnosti, ime, description, cijena, fk_id_trenera, spol, uzrast_od, uzrast_do) 
							VALUES (:id_aktivnosti, :ime, :description, :cijena, :fk_id_trenera, :spol, :uzrast_od, :uzrast_do)' );

		$st->execute( array( 'id_aktivnosti' => 1, 'ime' => 'HNK Daruvar', 'description' => 'Nogomet za uzrast 12 - 16 godina', 'cijena' => 20, 'fk_id_trenera' => 1, 'spol' => 'mješovito', 'uzrast_od' => 12, 'uzrast_do' => 16) ); 
		$st->execute( array( 'id_aktivnosti' => 2, 'ime' => 'OK Šibenik', 'description' => 'Odbojka za djevojčice 7 - 10 godina', 'cijena' => 18, 'fk_id_trenera' => 14, 'spol' => 'žensko', 'uzrast_od' => 7, 'uzrast_do' => 10) );
	}
	catch( PDOException $e ) { exit( "PDO error [splanner_aktivnosti]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_aktivnosti.<br />";
}


// ------------------------------------------
function seed_table_grupe()
{
	$db = DB::getConnection();

	// Ubaci neke prodaje unutra (ovo nije bas pametno ovako raditi, preko hardcodiranih id-eva usera i proizvoda)
	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_grupe(id_grupe, ime, description, cijena, spol, fk_id_aktivnosti) 
		VALUES (:id_grupe, :ime, :description, :cijena, :spol, :fk_id_aktivnosti)' );

		$st->execute( array( 'id_grupe' => 1, 'ime' => 'iskusni', 'description' => 'Nogomet za uzrast 12 - 16 godina, iskusni', 'cijena' => 20, 'spol' => 'muško', 'fk_id_aktivnosti' => 1) ); 
		$st->execute( array( 'id_grupe' => 2, 'ime' => 'nova grupa', 'description' => 'Nogomet za uzrast 12 - 16 godina, nova grupa', 'cijena' => 20, 'spol' => 'mješovito', 'fk_id_aktivnosti' => 1) ); 
		$st->execute( array( 'id_grupe' => 3, 'ime' => 'OK junior', 'description' => 'Odbojka za djevojčice 7 - 10 godina', 'cijena' => 18, 'spol' => 'žensko', 'fk_id_aktivnosti' => 2) ); 
	}
	catch( PDOException $e ) { exit( "PDO error [splanner_grupe]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_grupe.<br />";
}

function seed_table_obavijesti()
{
	$db = DB::getConnection();

	// Ubaci neke prodaje unutra (ovo nije bas pametno ovako raditi, preko hardcodiranih id-eva usera i proizvoda)
	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_obavijesti(id_obavijest, id_grupe_fk, datum, vrijeme, comment) 
		VALUES (:id_obavijest, :id_grupe_fk, :datum, :vrijeme, :comment)' );

		$st->execute( array( 'id_obavijest' => 1, 'id_grupe_fk' => 1, 'datum' => '2025-07-07', 'vrijeme' => '13:15:30', 'comment' => 'Sljedeća utakmica je 18.07. u 17 sati u Daruvaru. Dođite u svlacionice najkasnije do 16 sati.') ); 
		$st->execute( array( 'id_obavijest' => 2, 'id_grupe_fk' => 1, 'datum' => '2025-07-07', 'vrijeme' => '13:10:30', 'comment' => 'Sljedeći trening će se izvanredno održati u četvrtak 09.07. u 18 sati.') ); 
		$st->execute( array( 'id_obavijest' => 3, 'id_grupe_fk' => 3, 'datum' => '2025-07-08', 'vrijeme' => '11:15:12', 'comment' => 'Današnji termin je otkazan zbog bolesti trenerice') ); 
	}
	catch( PDOException $e ) { exit( "PDO error [dz2_sales]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_obavijesti<br />";
}

?> 
