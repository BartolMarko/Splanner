<?php


require_once __DIR__ . '/../app/database/db.class.php';

class SplannerService
{
    const USERS_TABLE = 'splanner_korisnici';
    const OBAVIJESTI_TABLE = 'splanner_obavijesti';
    const GRUPE_TABLE = 'splanner_grupe';
    const AKTIVNOSTI_TABLE = 'splanner_aktivnosti';
    const PRIPADNOST_TABLE = 'splanner_pripadnost';
	const AZURNI_TERMINI_TABLE = 'splanner_azurni_termini';


	public function obrisiGrupu($idGrupa) {
		try {
			$db = DB::getConnection();
			
			$st = $db->prepare('DELETE FROM splanner_redovni_termini WHERE id_grupe_fk = :id');
			$st->execute(array('id' => intval($idGrupa)));

			$st = $db->prepare('DELETE FROM splanner_azurni_termini WHERE id_grupe_fk = :id');
			$st->execute(array('id' => intval($idGrupa)));
			
			$st = $db->prepare('DELETE FROM splanner_pripadnost WHERE id_grupe_fk = :id');
			$st->execute(array('id' => intval($idGrupa)));

			$st = $db->prepare('DELETE FROM splanner_grupe WHERE id_grupe = :id');
			$st->execute(array('id' => intval($idGrupa)));
			
		} catch(PDOException $e) {
			exit('PDO error ' . $e->getMessage());
		}
	}


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
		else if (!password_verify($password, $row['password_hash']) || $row['username'] !== $username) {
			return 0; 
		} else {
			return $row;
		}
	}

	// dohvaca ime korisnika s nekim id-om
    function getImePrezimeKorisnikaFormId( $id )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT ime, prezime FROM ' . self::USERS_TABLE . ' WHERE id_korisnici=:id' );
			$st->execute( ['id' => $id] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return $row['ime'] . ' ' . $row['prezime'];
	}

	// dohvaca ime korisnika 
	public function getImenaKorisnika($ids, $imePrezime = false)
	{
		if (empty($ids)) return [];
	
		try {
			$db = DB::getConnection();
			$placeholders = implode(',', array_fill(0, count($ids), '?'));
			$st = $db->prepare('SELECT id_korisnici, username, ime, prezime FROM ' . self::USERS_TABLE . ' WHERE id_korisnici IN (' . $placeholders . ')');
			$st->execute($ids);
			
			$rezultat = [];
			while ($row = $st->fetch()) {
				if ($imePrezime) {
					$rezultat[$row['id_korisnici']] = $row['ime'] . ' ' . $row['prezime'];
				} else {
					$rezultat[$row['id_korisnici']] = $row['username'];
				}
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
		if( $row === false || $row['username'] !== $username)
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
	function addNewUser($username, $ime, $prezime, $password, $email, $oib, $uloga, $spol, $datum, $registration_sequence){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'INSERT INTO ' . self::USERS_TABLE . '(OIB, username, ime, prezime, password_hash, email, tip_korisnika, spol, datum_rodenja, registration_sequence, prima_obavijest, has_registered) VALUES ' .
								'(:OIB, :username, :ime, :prezime, :password_hash, :email, :tip_korisnika, :spol, :datum_rodenja, :registration_sequence, True, 0)' );
			$st->execute( array('OIB' => $oib,
								'username' => $username,
								'ime' => $ime,
								'prezime' => $prezime,
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

	public function obrisiSveGrupeTrenera($id_trenera)
	{
		$db = DB::getConnection();

		// obriši pripadnosti svih polaznika iz tih grupa
		$st = $db->prepare('
			DELETE p FROM splanner_pripadnost p
			JOIN splanner_grupe g ON p.id_grupe_fk = g.id_grupe
			JOIN splanner_aktivnosti a ON g.fk_id_aktivnosti = a.id_aktivnosti
			WHERE a.fk_id_trenera = :id
		');
		$st->execute(['id' => $id_trenera]);

		// obriši sve grupe
		$st = $db->prepare('
			DELETE g FROM splanner_grupe g
			JOIN splanner_aktivnosti a ON g.fk_id_aktivnosti = a.id_aktivnosti
			WHERE a.fk_id_trenera = :id
		');
		$st->execute(['id' => $id_trenera]);

		//  i same aktivnosti
		$st = $db->prepare('DELETE FROM splanner_aktivnosti WHERE fk_id_trenera = :id');
		$st->execute(['id' => $id_trenera]);
	}

	public function obrisiKorisnika($id_user)
	{
		$db = DB::getConnection();

		// Dohvati tip korisnika
		$st = $db->prepare('SELECT tip_korisnika FROM ' . self::USERS_TABLE . ' WHERE id_korisnici = :id');
		$st->execute(['id' => $id_user]);
		$tip = $st->fetchColumn();

		// Ako je trener, briši sve njegove grupe i aktivnosti
		if ($tip === 'trener') {
			$this->obrisiSveGrupeTrenera($id_user);
		}

		// Ako je roditelj, briši svu djecu
		if ($tip === 'roditelj') {
			// Prvo obriši pripadnosti djece
			$st = $db->prepare('
				DELETE p FROM splanner_pripadnost p
				JOIN ' . self::USERS_TABLE . ' d ON p.id_korisnik_fk = d.id_korisnici
				WHERE d.fk_id_roditelja = :id_roditelja
			');
			$st->execute(['id_roditelja' => $id_user]);

			// Onda obriši djecu
			$st = $db->prepare('DELETE FROM ' . self::USERS_TABLE . ' WHERE fk_id_roditelja = :id');
			$st->execute(['id' => $id_user]);
		}

		// Obriši pripadnosti ovog korisnika (ako je polaznik u nekoj grupi)
		$st = $db->prepare('DELETE FROM splanner_pripadnost WHERE id_korisnik_fk = :id');
		$st->execute(['id' => $id_user]);

		// Na kraju, obriši samog korisnika
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

	public function dodajDijete($id_roditelja, $username, $ime, $prezime, $oib, $email, $password, $spol, $datum)
	{
		$db = DB::getConnection();
		$st = $db->prepare(
			'INSERT INTO ' . self::USERS_TABLE . '
			(OIB, username, ime, prezime, password_hash, email, tip_korisnika, spol, datum_rodenja, registration_sequence, has_registered, fk_id_roditelja, prima_obavijest)
			VALUES
			(:oib, :username, :ime, :prezime, :hash, :email, "dijete", :spol, :datum, "", 1, :id_roditelja, 1)'
		);

		$st->execute([
			'oib' => $oib,
			'username' => $username,
			'ime' => $ime,
			'prezime' => $prezime,
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

		// Promijeni roditelja
		$st = $db->prepare('UPDATE ' . self::USERS_TABLE . ' SET prima_obavijest = :p WHERE id_korisnici = :id');
		$st->execute([
			'p' => $prima ? 1 : 0,
			'id' => $id_user
		]);

		// Promijeni svu djecu
		$st = $db->prepare('UPDATE ' . self::USERS_TABLE . ' SET prima_obavijest = :p WHERE fk_id_roditelja = :id');
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

		// Promijeni email roditelju
		$st = $db->prepare('UPDATE ' . self::USERS_TABLE . ' SET email = :email WHERE id_korisnici = :id');
		$st->execute([
			'email' => $noviEmail,
			'id' => $id_user
		]);

		// Promijeni email svoj djeci
		$st = $db->prepare('UPDATE ' . self::USERS_TABLE . ' SET email = :email WHERE fk_id_roditelja = :id');
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
				'SELECT t.datum_origin, t.datum_novi,
					t.vrijeme_poc_stari, t.vrijeme_poc_novi,
					t.vrijeme_kraj_stari, t.vrijeme_kraj_novi,
					t.dvorana,
					g.ime AS ime_grupe, g.id_grupe,
					a.ime AS ime_aktivnosti
				 FROM ' . self::AZURNI_TERMINI_TABLE . ' t
				 INNER JOIN ' . self::PRIPADNOST_TABLE . ' p ON t.id_grupe_fk = p.id_grupe_fk
				 INNER JOIN ' . self::GRUPE_TABLE . ' g ON p.id_grupe_fk = g.id_grupe
				 INNER JOIN ' . self::AKTIVNOSTI_TABLE . ' a ON g.fk_id_aktivnosti = a.id_aktivnosti
				 WHERE p.id_korisnik_fk = :userId
				 	AND (
						(t.datum_novi BETWEEN :datumOd AND :datumDo) OR
						((t.datum_origin BETWEEN :datumOd AND :datumDo) AND t.datum_novi IS NULL)
						)'
			);
			$st->execute([
				'userId' => $userId,
				'datumOd' => $datumOd,
				'datumDo' => $datumDo
			]);
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		return self::parseTerminiForRaspored($st->fetchAll());
	}

	public static function getTerminiForGrupa($id_grupe, $datumOd, $datumDo)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare(
				'SELECT t.datum_origin, t.datum_novi,
					t.vrijeme_poc_stari, t.vrijeme_poc_novi,
					t.vrijeme_kraj_stari, t.vrijeme_kraj_novi,
					t.dvorana,
					g.ime AS ime_grupe, g.id_grupe,
					a.ime AS ime_aktivnosti
				 FROM ' . self::AZURNI_TERMINI_TABLE . ' t
				 INNER JOIN ' . self::GRUPE_TABLE . ' g ON t.id_grupe_fk = g.id_grupe
				 INNER JOIN ' . self::AKTIVNOSTI_TABLE . ' a ON g.fk_id_aktivnosti = a.id_aktivnosti
				 WHERE t.id_grupe_fk = :id_grupe
				 	AND (
						(t.datum_novi BETWEEN :datumOd AND :datumDo) OR
						((t.datum_origin BETWEEN :datumOd AND :datumDo) AND t.datum_novi IS NULL)
						)'
			);
			$st->execute([
				'id_grupe' => $id_grupe,
				'datumOd' => $datumOd,
				'datumDo' => $datumDo
			]);
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$queryResult = $st->fetchAll();
		return self::parseTerminiForRaspored($queryResult);
	}

	private static function parseTerminiForRaspored($terminiRows) {
		$raspored = [];
		foreach ($terminiRows as $row) {

			$datum = $row['datum_novi'] ?: $row['datum_origin'];
			$vrijemePocetak = $row['vrijeme_poc_novi'] ?: $row['vrijeme_poc_stari'];
			$vrijemeKraj = $row['vrijeme_kraj_novi'] ?: $row['vrijeme_kraj_stari'];

			$raspored[] = [
				'datum' => $datum,
				'vrijeme_poc' => $vrijemePocetak,
				'vrijeme_kraj' => $vrijemeKraj,
				'dvorana' => $row['dvorana'],
				'ime_grupe' => $row['ime_grupe'],
				'id_grupe' => $row['id_grupe'],
				'ime_aktivnosti' => $row['ime_aktivnosti']
			];
		}
		return $raspored;
	}

	// Nikola upiti
		public function searchGrupe($ime, $grad, $spol, $mojUzrast) // <--- promjena
	{
		$db = DB::getConnection();

		$q = "SELECT * , g.ime AS grupa_ime, a.ime AS aktivnost_ime FROM splanner_grupe g
			JOIN splanner_aktivnosti a ON g.fk_id_aktivnosti = a.id_aktivnosti
			WHERE 1=1";

		$params = [];

		if ($ime !== '') {
			$q .= " AND a.ime LIKE :ime";
			$params[':ime'] = "%$ime%";
		}

		if ($grad !== '') {
			$q .= " AND a.grad LIKE :grad";
			$params[':grad'] = "%$grad%";
		}

		if ($spol !== 'mješovito') {
			$q .= " AND (g.spol = :spol OR g.spol = 'mješovito')";
			$params[':spol'] = $spol;
		}

		if ($mojUzrast !== null) {
			$q .= " AND g.uzrast_od <= :uzrast AND g.uzrast_do >= :uzrast";
			$params[':uzrast'] = $mojUzrast;
		}

		$st = $db->prepare($q);
		$st->execute($params);

		return $st->fetchAll();
	}


	public function getAktZaGrupu($idAkt){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 
				'SELECT * FROM splanner_aktivnosti WHERE id_aktivnosti = :id'
			);
			$st->execute( array( 'id' =>$idAkt) );
			$row=$st->fetch();
			return $row;
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}

	public function ispisiUseraSaAkt($userKojiIspisujem, $idAkt){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 
				'DELETE FROM ' . self::PRIPADNOST_TABLE . ' WHERE id_grupe_fk = :idg AND id_korisnik_fk = :idk'
			);
			$st->execute( array( 'idg' => $idAkt, 'idk'=>$userKojiIspisujem) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}

	public function deleteTermin($id){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 
				'SELECT fk_id_redovni_termini FROM splanner_azurni_termini WHERE id_azurni_termini = :id'
			);
			$st->execute( array( 'id' => $id) );
			$row=$st->fetch();
			if($row['fk_id_redovni_termini']!==null){
				$st = $db->prepare( 
					'DELETE FROM splanner_redovni_termini WHERE id_redovni_termini = :id'
				);
				$st->execute( array( 'id' => $row['fk_id_redovni_termini']) );
				$st = $db->prepare( 
					'DELETE FROM splanner_azurni_termini WHERE fk_id_redovni_termini = :id'
				);
				$st->execute( array( 'id' => $row['fk_id_redovni_termini']) );
			}
			$st = $db->prepare( 
				'DELETE FROM splanner_azurni_termini WHERE id_azurni_termini = :id'
			);
			$st->execute( array( 'id' => $id) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}


	public function deleteAktivnost($id){
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 
				'SELECT id_grupe FROM splanner_grupe WHERE fk_id_aktivnosti = :id'
			);
			$st->execute( array( 'id' => $id) );
			while($row=$st->fetch()){
				$st2 = $db->prepare( 
					'DELETE FROM ' . self::PRIPADNOST_TABLE . ' WHERE id_grupe_fk = :id'
				);
				$st2->execute( array( 'id' => $row['id_grupe']) );
				
				$st2 = $db->prepare( 
					'DELETE FROM splanner_redovni_termini WHERE id_grupe_fk = :id'
				);
				$st2->execute( array( 'id' => $row['id_grupe']) );

				$st2 = $db->prepare( 
					'DELETE FROM splanner_azurni_termini WHERE id_grupe_fk = :id'
				);
				$st2->execute( array( 'id' => $row['id_grupe']) );

				$st2 = $db->prepare(
				'DELETE FROM splanner_grupe WHERE id_grupe = :id'
				);
				$st2->execute(array('id' => $row['id_grupe']));
			}
			$st = $db->prepare( 
				'DELETE FROM splanner_aktivnosti WHERE id_aktivnosti = :id'
			);
			$st->execute( array( 'id' => $id) );

		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}

	public function makeTerminZaGrupu($id,$datum,$trener,$vrijeme_poc,$vrijeme_kraj,$dvorana,$tip_termina){
		try
	{
		$db = DB::getConnection();
		$id_aktivnosti=null;
		if($tip_termina==='redovan'){
		$st = $db->prepare( 
			'INSERT INTO splanner_redovni_termini (id_grupe_fk, id_trener_fk, dan, vrijeme_poc, vrijeme_kraj, dvorana) VALUES (:id_grup,:id_tren,:dan,:vrpoc,:vrkr,:dvo)'
		);
		$dan = date('l', strtotime($datum));
		$st->execute( array( 'id_grup' => $id, 'id_tren' => $trener, 'dan'=>$dan,'vrpoc'=>$vrijeme_poc,'vrkr'=>$vrijeme_kraj,'dvo'=>$dvorana) );
		$id_aktivnosti = $db->lastInsertId();	//ovo je fja iz PDO koja vrati najnoviji id indeksa (autoincrement)
	}
		if($id_aktivnosti===null){ //NIJE REDOVNI (NIJE SE TAMO DODALO NSTA) - ZNACI DATUM, NE DAN U TJEDNU
			$st = $db->prepare( 
				'INSERT INTO splanner_azurni_termini (fk_id_redovni_termini,id_grupe_fk, id_trener_fk, datum_origin, vrijeme_poc_stari, vrijeme_kraj_stari, dvorana) VALUES (:id_redovni,:id_grup,:id_tren,:dan,:vrpoc,:vrkr,:dvo)'
			);
			$dan = date('l', strtotime($datum));
			$st->execute( array( 'id_redovni'=>$id_aktivnosti,'id_grup' => $id, 'id_tren' => $trener, 'dan'=>$datum,'vrpoc'=>$vrijeme_poc,'vrkr'=>$vrijeme_kraj,'dvo'=>$dvorana) );
		}
		else {//REDOVAN, DAN U TJEDNU
			$danas = new DateTime();
			$endDatum = clone $danas;
			$endDatum->modify('next sunday')->modify('next sunday'); //modify na datetime mijenja dani datum in-place, ovo npr ga promijeni na sljedecu nedjelju, pa taj opet na sljedecu nedjelju toj nedjelji

			$imeDana = strtolower($datum); // npr 'wednesday'

			$interval = new DateInterval('P1D'); //interval po 1 dan
			$period = new DatePeriod($danas, $interval, $endDatum->modify('+1 day')); //ovaj +1 day jer po defaultu ne ukljucivo desni rub

			foreach ($period as $date) {
				if (strtolower($date->format('l')) === $imeDana) {
					// ako je ime dana jednako ovom koji unosim, unesem ga
					$st2 = $db->prepare(
						'INSERT INTO splanner_azurni_termini 
						(fk_id_redovni_termini, id_grupe_fk, id_trener_fk, datum_origin, vrijeme_poc_stari, vrijeme_kraj_stari, dvorana)
						VALUES (:id_redovni, :id_grup, :id_tren, :datum, :vrpoc, :vrkr, :dvo)'
					);
					$st2->execute([
						'id_redovni' => $id_aktivnosti,
						'id_grup' => $id,
						'id_tren' => $trener,
						'datum' => $date->format('Y-m-d'),
						'vrpoc' => $vrijeme_poc,
						'vrkr' => $vrijeme_kraj,
						'dvo' => $dvorana
					]);
				}
			}
		}
	}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}



	




	public function getGrupeZaAkt($idAkt){
		$db = DB::getConnection();
			$st = $db->prepare('SELECT * FROM splanner_grupe WHERE fk_id_aktivnosti = :id');
			$st->execute(['id' => $idAkt]);
			$polje=array();
			while($row=$st->fetch()){
				$polje[]=$row;
			}
			return $polje;
	}


	public function createGrupa($aktivnostId, $ime, $cijena, $dobMin,$dobMax, $spol){
		try
	{
		$db = DB::getConnection();

		$st = $db->prepare( 
			'INSERT INTO splanner_grupe (ime, cijena, spol, uzrast_od, uzrast_do, fk_id_aktivnosti) VALUES (:ime,:cijena,:spol,:uzod,:uzdo,:aktivnost)'
		);

		$st->execute( array( 'aktivnost' => $aktivnostId, 'ime' => $ime, 'spol'=>$spol,'uzod'=>$dobMin,'uzdo'=>$dobMax,'cijena'=>$cijena) );
		
	}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}

	public function updateAktivnost($id, $ime, $opis, $grad){
		try
	{
		$db = DB::getConnection();

		$st = $db->prepare( 
			'UPDATE splanner_aktivnosti SET ime=:ime, description=:opis, grad=:grad  WHERE id_aktivnosti=:id'
		);

		$st->execute( array( 'id' => $id, 'ime' => $ime, 'opis' => $opis, 'grad' => $grad ) );
		
	}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}

	public function getRedovniTerminiZaGrupu($idGrupe){
		try
	{
		$db = DB::getConnection();

		$st = $db->prepare( 
			'SELECT * FROM splanner_redovni_termini WHERE id_grupe_fk=:id'
		);

		$st->execute( array( 'id' => $idGrupe ) );
		
	}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$polje=array();
			while($row=$st->fetch()){
				$polje[]=$row;
			}
			return $polje;

	}

	public function getAzurniTerminiZaGrupu($idGrupe){
		try
	{
		$db = DB::getConnection();

		$st = $db->prepare( 
			'SELECT * FROM splanner_azurni_termini WHERE id_grupe_fk=:id ORDER BY datum_origin'
		);

		$st->execute( array( 'id' => $idGrupe ) );
		
	}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$polje=array();
			while($row=$st->fetch()){
				$polje[]=$row;
			}
			return $polje;

	}

	public function updateRedovniTermin($id, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana,$idAzur)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare(
				'UPDATE splanner_redovni_termini 
				 SET dan = :datum, vrijeme_poc = :vp, vrijeme_kraj = :vk, dvorana = :dvorana
				 WHERE id_redovni_termini = :id'
			);
			$st->execute([
				'id' => $id,
				'datum' => $datum,
				'vp' => $vrijeme_poc,
				'vk' => $vrijeme_kraj,
				'dvorana' => $dvorana
			]);


			$st = $db->prepare(
				'SELECT datum_origin 
				FROM splanner_azurni_termini 
				WHERE id_azurni_termini = :id_redovni' 
			);
			$st->execute(['id_redovni' => $idAzur]);
			$redak=$st->fetch();

			$datumPrvi = (new DateTime($redak['datum_origin']))->format('Y-m-d');
			$st = $db->prepare(
				'SELECT id_azurni_termini, datum_origin 
				FROM splanner_azurni_termini 
				WHERE fk_id_redovni_termini = :id_redovni 
				AND datum_origin >= :today'
			);
			$st->execute(['id_redovni' => $id, 'today' => $datumPrvi]);
			
			while ($row = $st->fetch()) {
				$this->updateAzurniTermin(
					$id,                   // id_redovni
					$row['id_azurni_termini'], // id_azur
					$datum,            // dan u tjednu, ali ovaj updateAzurniTermin zna do handleat
					$vrijeme_poc, 
					$vrijeme_kraj,
					$dvorana,
					0                       // $jelAzurno = 0 (znaci da je stvarni update, a ne izvanredni)
				);
			}

		} catch (PDOException $e) {
			throw new Exception("Greška u upisu termina: " . $e->getMessage());
		}
	}

	public function updateAzurniTermin($id, $id_azur, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $jelAzurno)
{
	try {
		$db = DB::getConnection();
		if($jelAzurno===1){ //izvanredni termin
		$st = $db->prepare(
			'UPDATE splanner_azurni_termini 
			 SET datum_novi = :datum, vrijeme_poc_novi = :vp, vrijeme_kraj_novi = :vk, dvorana = :dvorana
			 WHERE id_azurni_termini = :id_az'
		);
		$st->execute([
			'id_az' => $id_azur,
			'datum' => $datum,
			'vp' => $vrijeme_poc,
			'vk' => $vrijeme_kraj,
			'dvorana' => $dvorana
		]);
	}
		else{ //obican update, pa updateam samo staru vrijednost
			//NIJE AZURNO, ZATO MU SALJEM DAN U TJEDNU, A NE DATUM

			$st = $db->prepare(
				'SELECT datum_origin FROM splanner_azurni_termini WHERE id_azurni_termini = :id_az'
			);
			$st->execute(['id_az' => $id_azur]);
			$row = $st->fetch();
			if (!$row) {
				throw new Exception("Termin s ID-jem $id_azur nije pronađen.");
			}
		
			$stariDatum = new DateTime($row['datum_origin']);
			$today = new DateTime();
		
			// RAZLIKA U TJEDNIMA OD DANAS
			$weekDiff = (int)floor($stariDatum->diff($today)->days / 7);
		
			// IZRACUNAM NOVI DATUM, OCUVAJUCI RAZMAK U TJEDNIMA
			$noviDanUTj = strtolower($datum); // npr "subota"
			$noviDatum = clone $today;
			
			if ($weekDiff === 0) {
				// u trenutnom tjednu sam
				$noviDatum->modify('this ' . $noviDanUTj);
			} else {
				// buduci tjedni
				$noviDatum->modify('this ' . $noviDanUTj)->modify('+' . $weekDiff . ' weeks');
			}
		
			// ovdje pazim da ne premjestim u proslost (prebacim sljedeci tjedan ako je taj dan u tjednu vec prosao ovaj tjedan)
			if ($noviDatum < $today) {
				$noviDatum->modify('+1 week');
			}
		
			// i tek sad updateam azurni termin
			$st = $db->prepare(
				'UPDATE splanner_azurni_termini 
				 SET datum_origin = :datum, vrijeme_poc_stari = :vp, vrijeme_kraj_stari = :vk, 
					 dvorana = :dvorana
				 WHERE id_azurni_termini = :id_az'
			);
			$st->execute([
				'id_az' => $id_azur,
				'datum' => $noviDatum->format('Y-m-d'),
				'vp' => $vrijeme_poc,
				'vk' => $vrijeme_kraj,
				'dvorana' => $dvorana
			]);
		}
	} catch (PDOException $e) {
		throw new Exception("Greška u ažuriranju termina: " . $e->getMessage());
	}
}
		
		public function getGrupa($idGrupe)
		{
			$db = DB::getConnection();
			$st = $db->prepare('SELECT * FROM splanner_grupe WHERE id_grupe = :id');
			$st->execute(['id' => $idGrupe]);
			return $st->fetch();
		}

		function getGrupeForUser($idUser){
			try {
				$db = DB::getConnection();
		
				$st = $db->prepare( 
					'SELECT DISTINCT g.*
					FROM splanner_grupe g
					JOIN ' . self::PRIPADNOST_TABLE .' v ON g.id_grupe = v.id_grupe_fk
					JOIN splanner_aktivnosti a ON g.fk_id_aktivnosti = a.id_aktivnosti
					WHERE v.id_korisnik_fk = :id'
				); // vrati sve grupe korisnika
		
				$st->execute( array( 'id' => $idUser ) );
			}
			catch( PDOException $e ) {
				exit( 'PDO error [getGrupeForUser]: ' . $e->getMessage() );
			}
		
			$popisGrupa = array();
			while($row = $st->fetch()) {
				$popisGrupa[] = $row;
			}
			return $popisGrupa;
		}

		function getAktivnostiForUser($idUser){
			try
		{
			$db = DB::getConnection();
	
			$st = $db->prepare( 
				'SELECT DISTINCT a.*
			 FROM splanner_aktivnosti a
			 JOIN splanner_grupe g ON a.id_aktivnosti = g.fk_id_aktivnosti
			 JOIN ' . self::PRIPADNOST_TABLE .  ' v ON g.id_grupe = v.id_grupe_fk
			 WHERE v.id_korisnik_fk = :id'
			 ); //vratim sve aktivnosti u koje je upisana osoba, gledajuci u koje grupe je upisana i onda za koju aktivnost je ta grupa
			$st->execute( array( 'id' => $idUser ) );
			
		}
			catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
		
		$popisAkt=array();
		while($row=$st->fetch()){
			$popisAkt[]=$row;
		}
		return $popisAkt;
		}		


			function getAktivnostiByTrainer($idTrener){
				try
		{
			$db = DB::getConnection();
	
			$st = $db->prepare( 
				'SELECT * FROM splanner_aktivnosti WHERE fk_id_trenera = :id'
			);
			$st->execute( array( 'id' => $idTrener ) );
			
		}
			catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
		
		$popisAkt=array();
		while($row=$st->fetch()){
			$popisAkt[]=$row;
		}
		return $popisAkt;
	}
	
	function upisAkt($idTrener,$imeAkt,$descAkt,$gradAkt){
		try
	{
		$db = DB::getConnection();

		$st = $db->prepare( 
			'INSERT INTO splanner_aktivnosti (fk_id_trenera, description, grad, ime) VALUES (:id,:descr,:grad,:ime)'
		);

		$st->execute( array( 'id' => $idTrener, 'ime' => $imeAkt, 'descr' => $descAkt, 'grad' => $gradAkt ) );
		
	}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

	}

	function getDjecaByRoditelj($IDRod){
		try
		{
			$db = DB::getConnection();
	
			$st = $db->prepare( 
				'SELECT * FROM splanner_korisnici WHERE fk_id_roditelja = :id_roditelja'
			);
			$st->execute( array( 'id_roditelja' => $IDRod ) );
			
		}
			catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
		
		$popisDjece=array();
		while($row=$st->fetch()){
			$popisDjece[]=$row;
		}
		return $popisDjece;
	}

	function getAktByName($imeAkt){
		try
		{
			$db = DB::getConnection();
	
			$st = $db->prepare( 
				'SELECT * FROM splanner_aktivnosti WHERE ime LIKE :imeakt'
			);
			$regexImeAkt= "%" . $imeAkt . "%"; //% == 'bilo koliko znakova'
			$st->execute( array( 'imeakt' => $regexImeAkt ) );
			
		}
			catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
		
		$popisAkt=array();
		while($row=$st->fetch()){
			$popisAkt[]=[$row['ime'],$row['cijena'],$row['description']];
		}
		return $popisAkt;
	}
}

?>