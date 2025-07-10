<?php

class SplannerService
{
    const USERS_TABLE = 'splanner_korisnici';
    const OBAVIJESTI_TABLE = 'splanner_obavijesti';
    const GRUPE_TABLE = 'splanner_grupe';
    const AKTIVNOSTI_TABLE = 'splanner_aktivnosti';
    const PRIPADNOST_TABLE = 'splanner_pripadnost';
	const AZURNI_TERMINI_TABLE = 'splanner_azurni_termini';

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
			return $row;
		}
	}

	// dohvaca ime korisnika s nekim id-om
    function getImeKorisnikaFormId( $id )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT username FROM ' . self::USERS_TABLE . ' WHERE id_korisnici=:id' );
			$st->execute( ['id' => $id] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return $row['username'];
	}

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

	//dohvaćanje svih obavijesti
	function getAllObavijesti()
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

	function getObavijestiZaGrupe($grupe)
	{
		if (empty($grupe)) return [];
		try {
			$db = DB::getConnection();
			$ph = implode(',', array_fill(0, count($grupe), '?'));
			$st = $db->prepare(
				'SELECT * FROM ' . self::OBAVIJESTI_TABLE . ' WHERE id_grupe_fk IN (' . $ph . ')'
			);
			$st->execute($grupe);
		} catch (PDOException $e) {
			exit('PDO error ' . $e->getMessage());
		}
		$arr = [];
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

	function getObavijestiZaGrupuFromId($id)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare(
				'SELECT * FROM ' . self::OBAVIJESTI_TABLE . ' WHERE id_grupe_fk=:id'
			);
			$st->execute(array('id' => $id));
		} catch (PDOException $e) {
			exit('PDO error ' . $e->getMessage());
		}

		$arr = [];
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

	//dohvaćanje cijelog reda grupe iz id_grupa
	function getGrupaById( $id )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT * FROM ' . self::GRUPE_TABLE . ' WHERE id_grupe=:id' );
			$st->execute( ['id' => $id] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		return $row ?: null;
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

	// dohvaćanje cijelog reda aktivnosti na temelju id_grupa
	function getAktivnostByIdGrupa($id)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare('
				SELECT a.*
				FROM ' . self::GRUPE_TABLE . ' g
				JOIN ' . self::AKTIVNOSTI_TABLE . ' a ON g.fk_id_aktivnosti = a.id_aktivnosti
				WHERE g.id_grupe = :id
			');
			$st->execute(['id' => $id]);
		}
		catch(PDOException $e) {
			exit('PDO error ' . $e->getMessage());
		}

		$row = $st->fetch();
		return $row ?: null;
	}

	public function obrisiObavijest($id_obavijesti)
	{
		$db = DB::getConnection();

		$st = $db->prepare('DELETE FROM splanner_obavijesti WHERE id_obavijest = :id');
		$st->execute(['id' => $id_obavijesti]);
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

	public function dodajObavijest($id_grupe, $comment)
	{
		try {
			$db = DB::getConnection();

			$st = $db->prepare(
				'INSERT INTO ' . self::OBAVIJESTI_TABLE . ' (id_grupe_fk, datum, vrijeme, comment) 
				VALUES (:id_grupe, CURRENT_DATE, CURRENT_TIME, :comment)'
			);

			$st->execute([
				'id_grupe' => $id_grupe,
				'comment' => $comment
			]);
		} catch (PDOException $e) {
			exit('PDO error: ' . $e->getMessage());
		}
	}

	function dohvatiEmailoveZaGrupu($id_grupe)
	{
		$db = DB::getConnection();

		try {
			$st = $db->prepare(
				'SELECT k.email
				FROM splanner_korisnici k
				JOIN splanner_pripadnost p ON k.id_korisnici = p.id_korisnik_fk
				WHERE p.id_grupe_fk = :id_grupe AND k.prima_obavijest = TRUE'
			);

			$st->execute(['id_grupe' => $id_grupe]);

			$rezultat = [];
			while ($row = $st->fetch()) {
				$rezultat[] = $row['email'];
			}

			return $rezultat;
		}
		catch (PDOException $e) {
			exit("Greška kod dohvaćanja emailova: " . $e->getMessage());
		}
	}

	public function getClanoviGrupeIzListeKorisnika($id_grupe, $ids)
	{
		if (empty($ids)) return [];

		try {
			$db = DB::getConnection();
			$placeholders = implode(',', array_fill(0, count($ids), '?'));
			// Priprema upita s IN klauzulom
			$sql = 'SELECT id_korisnik_fk 
					FROM splanner_pripadnost 
					WHERE id_grupe_fk = ? 
					AND id_korisnik_fk IN (' . $placeholders . ')';

			$st = $db->prepare($sql);
			$st->execute(array_merge([$id_grupe], $ids));

			$rezultat = [];
			while ($row = $st->fetch()) {
				$rezultat[] = $row['id_korisnik_fk'];
			}
			return $rezultat;
		}
		catch (PDOException $e) {
			exit('PDO error ' . $e->getMessage());
		}
	}

	public function dodajKorisnikaUGrupu($id_korisnik, $id_grupa)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare('INSERT INTO splanner_pripadnost (id_grupe_fk, id_korisnik_fk) VALUES (:id_grupe, :id_korisnik)');
			$st->execute(['id_grupe' => $id_grupa, 'id_korisnik' => $id_korisnik]);
		} 
		catch (PDOException $e) {
			exit('PDO error: ' . $e->getMessage());
		}
	}

	public function obrisiKorisnikaIzGrupe($id_korisnik, $id_grupa)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare('
				DELETE FROM splanner_pripadnost
				WHERE id_grupe_fk = :id_grupe AND id_korisnik_fk = :id_korisnik
			');
			$st->execute(['id_grupe' => $id_grupa, 'id_korisnik' => $id_korisnik]);
		} 
		catch (PDOException $e) {
			exit('PDO error: ' . $e->getMessage());
		}
	}

	public function dohvatiIdeveClanovaGrupe($id_grupa)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare('
				SELECT id_korisnik_fk 
				FROM splanner_pripadnost 
				WHERE id_grupe_fk = :id_grupa
			');
			$st->execute(['id_grupa' => $id_grupa]);

			$rezultat = [];
			while ($row = $st->fetch()) {
				$rezultat[] = $row['id_korisnik_fk'];
			}

			return $rezultat;
		} 
		catch (PDOException $e) {
			exit('PDO error: ' . $e->getMessage());
		}
	}

	// dohvaca ime korisnika s nekim id-om
    function getKorisnikaFromId( $id )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT * FROM ' . self::USERS_TABLE . ' WHERE id_korisnici=:id' );
			$st->execute( ['id' => $id] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return $row;
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

	// --------- Raspored upiti
	static function getDjecaKorisnika( $userId ) {
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( "
				SELECT id_korisnici, username
				FROM " . self::USERS_TABLE . "
				WHERE fk_id_roditelja = :id_korisnika
			" );
			$st->execute( ['id_korisnika' => $userId] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		return $st->fetchAll();
	}

	public static function getGrupeTrenera( $userId )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( "
				SELECT g.id_grupe, g.ime
				FROM " . self::GRUPE_TABLE . " g
				INNER JOIN " . self::AKTIVNOSTI_TABLE . " a ON g.fk_id_aktivnosti = a.id_aktivnosti
				WHERE a.fk_id_trenera = :id_trenera
			" );
			$st->execute( ['id_trenera' => $userId] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		return $st->fetchAll();
	}

	public static function getTerminiForUser($userId, $datumOd, $datumDo)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare(
				'SELECT t.datum_novi as datum,
					t.vrijeme_poc_novi AS vrijeme_poc,
					t.vrijeme_kraj_novi as vrijeme_kraj,
					t.dvorana,
					g.ime AS ime_grupe, g.id_grupe,
					a.ime AS ime_aktivnosti
				 FROM ' . self::AZURNI_TERMINI_TABLE . ' t
				 INNER JOIN ' . self::PRIPADNOST_TABLE . ' p ON t.id_grupe_fk = p.id_grupe_fk
				 INNER JOIN ' . self::GRUPE_TABLE . ' g ON p.id_grupe_fk = g.id_grupe
				 INNER JOIN ' . self::AKTIVNOSTI_TABLE . ' a ON g.fk_id_aktivnosti = a.id_aktivnosti
				 WHERE p.id_korisnik_fk = :userId
				 	AND t.datum_novi BETWEEN :datumOd AND :datumDo'
			);
			$st->execute([
				'userId' => $userId,
				'datumOd' => $datumOd,
				'datumDo' => $datumDo
			]);
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		return $st->fetchAll();
	}

	public static function getTerminiForGrupa($id_grupe, $datumOd, $datumDo)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare(
				'SELECT t.datum_novi as datum,
					t.vrijeme_poc_novi AS vrijeme_poc,
					t.vrijeme_kraj_novi as vrijeme_kraj,
					t.dvorana,
					g.ime AS ime_grupe, g.id_grupe,
					a.ime AS ime_aktivnosti
				 FROM ' . self::AZURNI_TERMINI_TABLE . ' t
				 INNER JOIN ' . self::GRUPE_TABLE . ' g ON t.id_grupe_fk = g.id_grupe
				 INNER JOIN ' . self::AKTIVNOSTI_TABLE . ' a ON g.fk_id_aktivnosti = a.id_aktivnosti
				 WHERE t.id_grupe_fk = :id_grupe
				 	AND t.datum_novi BETWEEN :datumOd AND :datumDo'
			);
			$st->execute([
				'id_grupe' => $id_grupe,
				'datumOd' => $datumOd,
				'datumDo' => $datumDo
			]);
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		return $st->fetchAll();
	}
}

?>