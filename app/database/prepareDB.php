<?php

// Stvaramo tablice u bazi (ako već ne postoje od ranije).
require_once __DIR__ . '/db.class.php';

seed_table_korisnici();
seed_table_aktivnosti();
seed_table_grupe();
seed_table_obavijesti();
seed_table_pripadnost();
seed_table_termini();

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
		$st = $db->prepare( 'INSERT INTO splanner_korisnici(id_korisnici, OIB, username, password_hash, email, tip_korisnika, spol, datum_rodenja, registration_sequence, fk_id_roditelja, prima_obavijest, has_registered) VALUES (:id_korisnici, :OIB, :username, :password, \'a@b.com\', :tip, :spol, :datum_rodenja, :regseq, :fk_id_roditelja, True, :has )' );

		$st->execute( array( 'id_korisnici' => 1, 'OIB' => 12345678901, 'username' => 'mirko', 'password' => password_hash( 'mirkovasifra', PASSWORD_DEFAULT ), 'tip' => 'trener', 'spol' => 'muško', 'datum_rodenja' => '1990-07-07','regseq' => 'abc123', 'fk_id_roditelja' => NULL, 'has' => '1' ) );
		$st->execute( array( 'id_korisnici' => 2, 'OIB' => 12345678902, 'username' => 'slavko', 'password' => password_hash( 'slavkovasifra', PASSWORD_DEFAULT ), 'tip' => 'dijete', 'spol' => 'muško', 'datum_rodenja' => '2010-10-07', 'regseq' => 'def456', 'fk_id_roditelja' => 3, 'has' => '1' ) );
		$st->execute( array( 'id_korisnici' => 3, 'OIB' => 12345678903, 'username' => 'ana', 'password' => password_hash( 'aninasifra', PASSWORD_DEFAULT ), 'tip' => 'roditelj', 'spol' => 'žensko', 'datum_rodenja' => '1982-01-05', 'regseq' => 'mhffhm78', 'fk_id_roditelja' => NULL, 'has' => '1' ) );
		$st->execute( array( 'id_korisnici' => 4, 'OIB' => 12345678904, 'username' => 'maja', 'password' => password_hash( 'majinasifra', PASSWORD_DEFAULT ), 'tip' => 'dijete', 'spol' => 'žensko', 'datum_rodenja' => '2017-06-20', 'regseq' => 'ilzkutj98', 'fk_id_roditelja' => 5, 'has' => '1' ) );
		$st->execute( array( 'id_korisnici' => 5, 'OIB' => 12345678905, 'username' => 'pero', 'password' => password_hash( 'perinasifra', PASSWORD_DEFAULT ), 'tip' => 'roditelj', 'spol' => 'muško', 'datum_rodenja' => '1991-05-15', 'regseq' => '21354sd', 'fk_id_roditelja' => NULL, 'has' => '1' ) );
		$st->execute( array( 'id_korisnici' => 6, 'OIB' => 12345678906, 'username' => 'hana', 'password' => password_hash( 'haninasifra', PASSWORD_DEFAULT ), 'tip' => 'trener', 'spol' => 'žensko', 'datum_rodenja' => '1985-03-24', 'regseq' => '2gdr4sd', 'fk_id_roditelja' => NULL, 'has' => '1' ) );
	}
	catch( PDOException $e ) { exit( "PDO error [insert splanner_korisnici]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_korisnici.<br />";
}


// ------------------------------------------
function seed_table_aktivnosti()
{
	$db = DB::getConnection();

	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_aktivnosti(id_aktivnosti, ime, description, fk_id_trenera, grad) 
							VALUES (:id_aktivnosti, :ime, :description, :fk_id_trenera, :grad)' );

		$st->execute( array( 'id_aktivnosti' => 1, 'ime' => 'HNK Daruvar', 'description' => 'Nogomet za uzrast 12 - 16 godina', 'fk_id_trenera' => 1, 'grad' => 'Daruvar') ); 
		$st->execute( array( 'id_aktivnosti' => 2, 'ime' => 'OK Šibenik', 'description' => 'Odbojka za djevojčice 7 - 10 godina', 'fk_id_trenera' => 6, 'grad' => 'Šibenik') );
		$st->execute( array( 'id_aktivnosti' => 3, 'ime' => 'Joga', 'description' => 'Joga za sve uzraste', 'fk_id_trenera' => 6, 'grad' => 'Zagreb') );
		
		$st->execute( array( 'id_aktivnosti' => 3, 'ime' => 'Aerobik', 'description' => 'Aerobik opis jako dobar opis', 'cijena' => 22, 'fk_id_trenera' => 1, 'spol' => 'žensko', 'uzrast_od' => 7, 'uzrast_do' => 99) );
		$st->execute( array( 'id_aktivnosti' => 4, 'ime' => 'Macevanje', 'description' => 'opis odličan opis', 'cijena' => 50, 'fk_id_trenera' => 1, 'spol' => 'žensko', 'uzrast_od' => 7, 'uzrast_do' => 99) );
		$st->execute( array( 'id_aktivnosti' => 5, 'ime' => 'Life coaching', 'description' => 'neki zivotni savjet opis', 'cijena' => 150, 'fk_id_trenera' => 1, 'spol' => 'žensko', 'uzrast_od' => 7, 'uzrast_do' => 99) );
	}
	catch( PDOException $e ) { exit( "PDO error [splanner_aktivnosti]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_aktivnosti.<br />";
}


// ------------------------------------------
function seed_table_grupe()
{
	$db = DB::getConnection();

	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_grupe(id_grupe, ime, cijena, spol, uzrast_od, uzrast_do, fk_id_aktivnosti) 
		VALUES (:id_grupe, :ime, :cijena, :spol, :uzrast_od, :uzrast_do, :fk_id_aktivnosti)' );

		$st->execute( array( 'id_grupe' => 1, 'ime' => 'Iskusni', 'cijena' => 20, 'spol' => 'muško', 'uzrast_od' => 12, 'uzrast_do' => 16, 'fk_id_aktivnosti' => 1) ); 
		$st->execute( array( 'id_grupe' => 2, 'ime' => 'Nova grupa', 'cijena' => 20, 'spol' => 'mješovito', 'uzrast_od' => 12, 'uzrast_do' => 16, 'fk_id_aktivnosti' => 1) ); 
		$st->execute( array( 'id_grupe' => 3, 'ime' => 'OK junior', 'cijena' => 18, 'spol' => 'žensko', 'uzrast_od' => 7, 'uzrast_do' => 10, 'fk_id_aktivnosti' => 2) ); 
		$st->execute( array( 'id_grupe' => 4, 'ime' => 'Joga žene', 'cijena' => 25, 'spol' => 'žensko', 'uzrast_od' => NULL, 'uzrast_do' => NULL, 'fk_id_aktivnosti' => 3) ); 
		$st->execute( array( 'id_grupe' => 5, 'ime' => 'Joga muškarci', 'cijena' => 25, 'spol' => 'muško', 'uzrast_od' => NULL, 'uzrast_do' => NULL, 'fk_id_aktivnosti' => 3) ); 
		
		$st->execute( array( 'id_grupe' => 4, 'ime' => 'aerobik - grupa', 'description' => 'opis aerobik grupe', 'cijena' => 20, 'spol' => 'muško', 'fk_id_aktivnosti' => 3) ); 
		$st->execute( array( 'id_grupe' => 5, 'ime' => 'macevanje - grupa', 'description' => 'macevanje nova grupa', 'cijena' => 20, 'spol' => 'mješovito', 'fk_id_aktivnosti' => 4) ); 
		$st->execute( array( 'id_grupe' => 6, 'ime' => 'life coach - grupa', 'description' => 'life coaching grupica', 'cijena' => 18, 'spol' => 'žensko', 'fk_id_aktivnosti' => 5) );
	}
	catch( PDOException $e ) { exit( "PDO error [splanner_grupe]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_grupe.<br />";
}

function seed_table_obavijesti()
{
	$db = DB::getConnection();

	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_obavijesti(id_obavijest, id_grupe_fk, datum, vrijeme, comment) 
		VALUES (:id_obavijest, :id_grupe_fk, :datum, :vrijeme, :comment)' );

		$st->execute( array( 'id_obavijest' => 1, 'id_grupe_fk' => 1, 'datum' => '2025-07-07', 'vrijeme' => '13:15:30', 'comment' => 'Sljedeća utakmica je 18.07. u 17 sati u Daruvaru. Dođite u svlacionice najkasnije do 16 sati.') ); 
		$st->execute( array( 'id_obavijest' => 2, 'id_grupe_fk' => 1, 'datum' => '2025-07-07', 'vrijeme' => '13:10:30', 'comment' => 'Sljedeći trening će se izvanredno održati u četvrtak 09.07. u 18 sati.') ); 
		$st->execute( array( 'id_obavijest' => 3, 'id_grupe_fk' => 3, 'datum' => '2025-07-08', 'vrijeme' => '11:15:12', 'comment' => 'Današnji terming je otkazan zbog bolesti trenerice') ); 
		$st->execute( array( 'id_obavijest' => 4, 'id_grupe_fk' => 4, 'datum' => '2025-07-09', 'vrijeme' => '10:34:12', 'comment' => 'Današnji terming je otkazan zbog bolesti trenera') ); 
	}
	catch( PDOException $e ) { exit( "PDO error [splanner_obavijesti]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_obavijesti<br />";
}

function seed_table_pripadnost()
{
	$db = DB::getConnection();
	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_pripadnost(id_veze, id_grupe_fk, id_korisnik_fk) 
		VALUES (:id_veze, :id_grupe_fk, :id_korisnik_fk)' );

		$st->execute( array( 'id_veze' => 1, 'id_grupe_fk' => 1, 'id_korisnik_fk' => 2) ); 
		$st->execute( array( 'id_veze' => 2, 'id_grupe_fk' => 4, 'id_korisnik_fk' => 3) ); 
		$st->execute( array( 'id_veze' => 3, 'id_grupe_fk' => 3, 'id_korisnik_fk' => 4) ); 
		$st->execute( array( 'id_veze' => 4, 'id_grupe_fk' => 5, 'id_korisnik_fk' => 5) ); 
	}
	catch( PDOException $e ) { exit( "PDO error [splanner_pripadnost]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_pripadnost<br />";
}

function seed_table_termini()
{
	$db = DB::getConnection();

	// Ubaci neke prodaje unutra (ovo nije bas pametno ovako raditi, preko hardcodiranih id-eva usera i proizvoda)
	try
	{
		$st = $db->prepare( 'INSERT INTO splanner_redovni_termini(id_grupe_fk, id_trener_fk, datum, vrijeme_poc, vrijeme_kraj, dvorana, comment) 
		VALUES (:id_grupe_fk, :id_trener_fk, :datum, :vrijeme_poc, :vrijeme_kraj, :dvorana, :comment)' );

		$st->execute( array( 'id_grupe_fk' => 4, 'id_trener_fk' => 1, 'datum' => '2025-07-12', 'vrijeme_poc' => '08:00:00', 'vrijeme_kraj' => '14:00:00', 'dvorana' => 'Dvorana 1', 'comment' => 'Termin A') );
		$st->execute( array( 'id_grupe_fk' => 5, 'id_trener_fk' => 1, 'datum' => '2025-07-12', 'vrijeme_poc' => '09:00:00', 'vrijeme_kraj' => '10:30:00', 'dvorana' => 'Dvorana 2', 'comment' => 'Termin B') );
		$st->execute( array( 'id_grupe_fk' => 6, 'id_trener_fk' => 1, 'datum' => '2025-07-12', 'vrijeme_poc' => '11:15:00', 'vrijeme_kraj' => '12:30:00', 'dvorana' => 'Dvorana 3', 'comment' => 'Termin C') );
		$st->execute( array( 'id_grupe_fk' => 4, 'id_trener_fk' => 1, 'datum' => '2025-07-12', 'vrijeme_poc' => '11:45:00', 'vrijeme_kraj' => '13:20:00', 'dvorana' => 'Dvorana 4', 'comment' => 'Termin D') );
		$st->execute( array( 'id_grupe_fk' => 5, 'id_trener_fk' => 1, 'datum' => '2025-07-12', 'vrijeme_poc' => '12:30:00', 'vrijeme_kraj' => '14:30:00', 'dvorana' => 'Dvorana 5', 'comment' => 'Termin E') );
		$st->execute( array( 'id_grupe_fk' => 6, 'id_trener_fk' => 1, 'datum' => '2025-07-12', 'vrijeme_poc' => '18:00:00', 'vrijeme_kraj' => '19:00:00', 'dvorana' => 'Dvorana 6', 'comment' => 'Termin F') );
		
	}
	catch( PDOException $e ) { exit( "PDO error [termini]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_redovni_termini<br />";
}

?> 
