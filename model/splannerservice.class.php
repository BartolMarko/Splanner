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
	function checkRegSeq($reg_seq){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT * FROM ' . self::USERS_TABLE . ' WHERE registration_sequence=:registration_sequence' );
			$st->execute( array( 'registration_sequence' => $reg_seq ) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return true;
		else
			return false;
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

	//dohvaćanje svih obavijesti
	public function getAllObavijesti()
	{
		$povezani = $this->dohvatiPovezaneKorisnike($_SESSION['id_user']);
		if (empty($povezani)) return [];

		$grupe = $this->dohvatiGrupeZaKorisnike($povezani);
		if (empty($grupe)) return [];

		try {
			$db = DB::getConnection();
			$placeholders = implode(',', array_fill(0, count($grupe), '?'));
			$query = 'SELECT * FROM ' . self::OBAVIJESTI_TABLE . ' WHERE id_grupe_fk IN (' . $placeholders . ')';

			$st = $db->prepare($query);
			$st->execute($grupe);
		} catch (PDOException $e) {
			exit('PDO error ' . $e->getMessage());
		}

		$arr = array();
		while ($row = $st->fetch()) {
			$arr[] = new Obavijest(
				$row['id_obavijest'],
				$row['id_grupe_fk'],
				$this->getGrupaImeById($row['id_grupe_fk']),
				$this->getAktivnostImeByIdGrupa($row['id_grupe_fk']),
				$row['datum'],
				$row['vrijeme'],
				$row['comment']
			);
		}

		return $arr;
	}


	//dohvaćanje imena grupe iz id_grupa (za ispis na koju grupu se obavijest odnosi)
	function getGrupaImeById( $id )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT ime FROM ' . self::GRUPE_TABLE . ' WHERE id_grupe=:id' );
			$st->execute( ['id' => $id] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return $row['ime'];
	}

	//dohvaćanje imena aktivnosti iz id_grupa (za ispis na koju aktivnost se obavijest odnosi)
	function getAktivnostImeByIdGrupa( $id )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( '
				SELECT a.ime AS ime_aktivnosti
				FROM ' . self::GRUPE_TABLE . ' g
				JOIN ' . self::AKTIVNOSTI_TABLE . ' a ON g.fk_id_aktivnosti = a.id_aktivnosti
				WHERE g.id_grupe = :id' );
			$st->execute( ['id' => $id] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return $row['ime_aktivnosti'];
	}

	//ako je id dijeteta, funkcija vraća samo njegov id, a ako je id roditelja, onda se vraćaju idijevi njega i svi idijevi njegove djece
	function dohvatiPovezaneKorisnike($id_korisnika)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare('SELECT tip_korisnika FROM ' . self::USERS_TABLE . ' WHERE id_korisnici = :id');
			$st->execute(['id' => $id_korisnika]);
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();

		if ($row === false) {
			return [];
		}

		$tip = $row['tip_korisnika'];

		if ($tip === 'dijete') {
			return [$id_korisnika];
		} 
		elseif ($tip === 'roditelj') {
			try
			{
				$db = DB::getConnection();
				$st = $db->prepare('SELECT id_korisnici FROM ' . self::USERS_TABLE . ' WHERE fk_id_roditelja = :id');
				$st->execute(['id' => $id_korisnika]);
			}
			catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

			$rezultat = [$id_korisnika]; 

			while ($red = $st->fetch()) {
				$rezultat[] = $red['id_korisnici'];
			}

			return $rezultat;
		}

		return [$id_korisnika];
	}

	function dohvatiGrupeZaKorisnike($id_korisnici)
	{
		if (empty($id_korisnici)) {
			return [];
		}

		$placeholders = implode(',', array_fill(0, count($id_korisnici), '?'));

		$query = 'SELECT DISTINCT id_grupe_fk FROM ' . self::PRIPADNOST_TABLE . ' WHERE id_korisnik_fk IN (' . $placeholders . ')';

		try
		{
			$db = DB::getConnection();
			$st = $db->prepare($query);
			$st->execute($id_korisnici);
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$rezultat = [];
		while ($red = $st->fetch()) {
			$rezultat[] = $red['id_grupe_fk'];
		}

		return $rezultat;
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


	
}

?>