<?php

class SplannerService
{
     const USERS_TABLE = 'splanner_korisnici';
    const OBAVIJESTI_TABLE = 'splanner_obavijesti';
    const GRUPE_TABLE = 'splanner_grupe';
    const AKTIVNOSTI_TABLE = 'splanner_aktivnosti';
    const PRIPADNOST_TABLE = 'splanner_pripadnost';

	// ulogiravanje - postavljanje sessiona ili izbacivanje greške
	function checkLogin($username, $password)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare('SELECT * FROM ' . self::USERS_TABLE . ' WHERE username=:username');
			$st->execute(['username' => $username]);
		}
		catch(PDOException $e) { exit('PDO error ' . $e->getMessage()); }

		$row = $st->fetch();

		if (!$row)
			return 0; 
		else if ($row['has_registered'] === '0') {
			return 2;
		}
		else if (!password_verify($password, $row['password_hash'])) {
			return 0; 
		} else {
			return $row;  // <---- ode je promjena! umjesto 1 vracamo cijeli red da poslije mogu dohvatiti tip korisnika
		}
	}

	// dohvaca id korisnika s nekim imenom
    // function getUserIdByName( $username )
	// {
	// 	try
	// 	{
	// 		$db = DB::getConnection();
	// 		$st = $db->prepare( 'SELECT id_korisnici FROM ' . self::USERS_TABLE . ' WHERE username=:username' );
	// 		$st->execute( ['username' => $username] );
	// 	}
	// 	catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

	// 	$row = $st->fetch();
	// 	if( $row === false )
	// 		return null;
	// 	else
	// 		return $row['id_korisnici'];
	// }

	// dohvaca ime korisnika 
	public function getImenaKorisnika($ids)
	{
		if (empty($ids)) return [];
	
		try {
			$db = DB::getConnection();
			$placeholders = implode(',', array_fill(0, count($ids), '?'));
			$st = $db->prepare('SELECT id_korisnici, username FROM ' . self::USERS_TABLE . ' WHERE id_korisnici IN (' . $placeholders . ')');
			$st->execute($ids);
			
			$rezultat = [];
			while ($row = $st->fetch()) {
				$rezultat[$row['id_korisnici']] = $row['username'];
			}
			return $rezultat;
		}
		catch (PDOException $e) {
			exit('PDO error ' . $e->getMessage());
		}
	}
	

	// provjera ako se neko korisničko ime već koristi
	function checkIfUsernameOccupied( $username ){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare('SELECT * FROM ' . self::USERS_TABLE . ' WHERE username=:username');
			$st->execute( ['username' => $username] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return false;
		else
			return true;
	}

	// provjera ako slučajno nije generiran jednaki reg. seq. 
	public function checkRegSeq($seq)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare('SELECT 1 FROM ' . self::USERS_TABLE . ' WHERE registration_sequence = :seq');
			$st->execute(['seq' => $seq]);

			return $st->fetch() !== false;
		}
		catch (PDOException $e) {
			exit('PDO error [checkRegSeq]: ' . $e->getMessage());
		}
	}

	// ažuriranje baze nakon kliknutog linka u mailu
	function updateRegSeq($reg_seq){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'UPDATE ' . self::USERS_TABLE . ' SET has_registered=1 WHERE registration_sequence=:registration_sequence' );
			$st->execute( array( 'registration_sequence' => $reg_seq ) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}

	// dodavanje novog korisnika / trenera u bazu prilikom registracije
	function addNewUser($username, $password, $email, $oib, $uloga, $spol, $datum, $registration_sequence){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'INSERT INTO ' . self::USERS_TABLE . '(OIB, username, password_hash, email, tip_korisnika, spol, datum_rodenja, registration_sequence, prima_obavijest, has_registered) VALUES ' .
								'(:OIB, :username, :password_hash, :email, :tip_korisnika, :spol, :datum_rodenja, :registration_sequence, True, 0)' );
			$st->execute( array('OIB' => $oib,
								'username' => $username, 
								'password_hash' => password_hash( $password, PASSWORD_DEFAULT ), 
								'email' => $email, 
								'tip_korisnika' => $uloga, 
								'spol' => $spol, 
								'datum_rodenja' => $datum, 
								'registration_sequence'  => $registration_sequence ) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}
	
	//------- Jelena = postavke ----------------------------
	// Provjera postoji li korisnicko ime
	public function checkIfUsernameExists($username)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare('SELECT COUNT(*) FROM ' . self::USERS_TABLE . ' WHERE username = :username');
			$st->execute(['username' => $username]);
		}
		catch (PDOException $e) { exit('PDO error ' . $e->getMessage()); }

		return $st->fetchColumn() > 0;
	}

	// Azuriranje korisnickog imena
	public function updateUsername($id_user, $newUsername)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare('UPDATE ' . self::USERS_TABLE . ' SET username = :username WHERE id_korisnici = :id_korisnici');
			$st->execute([
				'username' => $newUsername,
				'id_korisnici' => $id_user
			]);
		}
		catch (PDOException $e) { exit('PDO error ' . $e->getMessage()); }
	}

	// Provjera stare lozinke
	public function provjeriLozinku($id_user, $staraLozinka)
	{
		$db = DB::getConnection();
		$st = $db->prepare('SELECT password_hash FROM ' . self::USERS_TABLE . ' WHERE id_korisnici = :id');
		$st->execute(['id' => $id_user]);

		$hash = $st->fetchColumn();
		return password_verify($staraLozinka, $hash);
	}

	// Promjena lozinke
	public function promijeniLozinku($id_user, $novaLozinka)
	{
		$db = DB::getConnection();
		$st = $db->prepare('UPDATE ' . self::USERS_TABLE . ' SET password_hash = :h WHERE id_korisnici = :id');
		$st->execute([
			'h' => password_hash($novaLozinka, PASSWORD_DEFAULT),
			'id' => $id_user
		]);
	}

	//brisanje racuna
	public function obrisiKorisnika($id_user)
	{
		$db = DB::getConnection();
		$st = $db->prepare('DELETE FROM ' . self::USERS_TABLE . ' WHERE id_korisnici = :id');
		$st->execute(['id' => $id_user]);
	}

	//za dodavanje novog clana
	public function dohvatiEmailKorisnika($id_user)
	{
		$db = DB::getConnection();
		$st = $db->prepare('SELECT email FROM ' . self::USERS_TABLE . ' WHERE id_korisnici = :id');
		$st->execute(['id' => $id_user]);

		return $st->fetchColumn();
	}

	public function dodajDijete($id_roditelja, $username, $oib, $email, $password, $spol, $datum)
	{
		$db = DB::getConnection();
		$st = $db->prepare(
			'INSERT INTO ' . self::USERS_TABLE . '
			(OIB, username, password_hash, email, tip_korisnika, spol, datum_rodenja, registration_sequence, has_registered, fk_id_roditelja)
			VALUES
			(:oib, :username, :hash, :email, "dijete", :spol, :datum, "", 1, :id_roditelja)'
		);

		$st->execute([
			'oib' => $oib,
			'username' => $username,
			'hash' => password_hash($password, PASSWORD_DEFAULT),
			'email' => $email,
			'spol' => $spol,
			'datum' => $datum,
			'id_roditelja' => $id_roditelja
		]);
	}


	public function dohvatiDjecu($id_roditelja)
	{
		$db = DB::getConnection();
		$st = $db->prepare('SELECT id_korisnici, username FROM ' . self::USERS_TABLE . ' WHERE fk_id_roditelja = :id');
		$st->execute(['id' => $id_roditelja]);
		$rezultat = $st->fetchAll();
    	return $rezultat;
	}

	public function provjeriDijeteId($id_roditelja, $id_djeteta)
	{
		$db = DB::getConnection();
		$st = $db->prepare('SELECT COUNT(*) FROM ' . self::USERS_TABLE . ' WHERE id_korisnici = :id AND fk_id_roditelja = :roditelj');
		$st->execute(['id' => $id_djeteta, 'roditelj' => $id_roditelja]);
		return $st->fetchColumn() > 0;
	}

	public function obrisiDijeteId($id_roditelja, $id_djeteta)
	{
		$db = DB::getConnection();
		$st = $db->prepare('DELETE FROM ' . self::USERS_TABLE . ' WHERE id_korisnici = :id AND fk_id_roditelja = :roditelj');
		$st->execute(['id' => $id_djeteta, 'roditelj' => $id_roditelja]);
	}

	public function postaviPrimaObavijesti($id_user, $prima)
	{
		$db = DB::getConnection();
		$st = $db->prepare('UPDATE ' . self::USERS_TABLE . ' SET prima_obavijest = :p WHERE id_korisnici = :id');
		$st->execute([
			'p' => $prima ? 1 : 0,
			'id' => $id_user
		]);
	}

	// dohvat trenutnog stanja
	public function dohvatiPrimaObavijesti($id_user)
	{
		$db = DB::getConnection();
		$st = $db->prepare('SELECT prima_obavijest FROM ' . self::USERS_TABLE . ' WHERE id_korisnici = :id');
		$st->execute(['id' => $id_user]);
		return $st->fetchColumn();
	}

	// Dohvati grupe u koje je korisnik upisan
	public function dohvatiGrupeZaKorisnika($id_korisnik)
	{	
		$db = DB::getConnection();
		$st = $db->prepare(
			'SELECT g.id_grupe, g.ime, g.cijena, a.ime AS aktivnost_ime
			FROM splanner_grupe g
			JOIN splanner_aktivnosti a ON g.fk_id_aktivnosti = a.id_aktivnosti
			JOIN splanner_pripadnost p ON p.id_grupe_fk = g.id_grupe
			WHERE p.id_korisnik_fk = :id'
		);
		$st->execute(['id' => $id_korisnik]);

		return $st->fetchAll();
	}

	public function dohvatiRoditeljaOdDjeteta($id_djeteta)
	{
		$db = DB::getConnection();
		$st = $db->prepare('
			SELECT r.username, r.email, r.prima_obavijest
			FROM ' . self::USERS_TABLE . ' d
			JOIN ' . self::USERS_TABLE . ' r ON d.fk_id_roditelja = r.id_korisnici
			WHERE d.id_korisnici = :id
		');
		$st->execute(['id' => $id_djeteta]);

		return $st->fetch(PDO::FETCH_ASSOC);
	}

		public function dohvatiUkupnuZaraduTrenera($id_trenera)
	{
		$db = DB::getConnection();
		$st = $db->prepare('
			SELECT SUM(g.cijena) as ukupno
			FROM ' . self::GRUPE_TABLE . ' g
			JOIN ' . self::AKTIVNOSTI_TABLE . ' a ON g.fk_id_aktivnosti = a.id_aktivnosti
			WHERE a.fk_id_trenera = :id
		');
		$st->execute(['id' => $id_trenera]);
		return $st->fetchColumn() ?: 0;
	}

	public function dohvatiZaraduPoGrupamaTrenera($id_trenera)
	{
		$db = DB::getConnection();
		$st = $db->prepare('
			SELECT 
				g.ime AS ime_grupe,
				g.id_grupe,   
				g.cijena,
				COUNT(p.id_korisnik_fk) AS broj_polaznika
			FROM ' . self::GRUPE_TABLE . ' g
			JOIN ' . self::AKTIVNOSTI_TABLE . ' a ON g.fk_id_aktivnosti = a.id_aktivnosti
			LEFT JOIN ' . self::PRIPADNOST_TABLE . ' p ON p.id_grupe_fk = g.id_grupe
			WHERE a.fk_id_trenera = :id
			GROUP BY g.id_grupe, g.ime, g.cijena
			HAVING broj_polaznika > 0
		');
		$st->execute(['id' => $id_trenera]);
		return $st->fetchAll();
	}


	public function promijeniEmail($id_user, $noviEmail)
	{
		$db = DB::getConnection();
		$st = $db->prepare('UPDATE ' . self::USERS_TABLE . ' SET email = :email WHERE id_korisnici = :id');
		$st->execute([
			'email' => $noviEmail,
			'id' => $id_user
		]);
	}

	public function emailExists($email)
	{
		$db = DB::getConnection();
		$st = $db->prepare('SELECT COUNT(*) FROM ' . self::USERS_TABLE . ' WHERE email = :email');
		$st->execute(['email' => $email]);
		return $st->fetchColumn() > 0;
	}


	
}

?>