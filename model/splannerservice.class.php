<?php

require_once __DIR__ . '/../app/database/db.class.php';

class SplannerService
{
    const USERS_TABLE = 'splanner_korisnici';

	public function makeTerminZaGrupu($id,$datum,$trener,$vrijeme_poc,$vrijeme_kraj,$dvorana,$comment,$tip_termina){
		try
	{
		$db = DB::getConnection();
		$id_aktivnosti=null;
		if($tip_termina==='redovni'){
		$st = $db->prepare( 
			'INSERT INTO splanner_redovni_termini (id_grupe_fk, id_trener_fk, dan, vrijeme_poc, vrijeme_kraj, dvorana, comment) VALUES (:id_grup,:id_tren,:dan,:vrpoc,:vrkr,:dvo,:kom)'
		);
		$dan = date('l', strtotime($datum));
		$st->execute( array( 'id_grup' => $id, 'id_tren' => $trener, 'dan'=>$dan,'vrpoc'=>$vrijeme_poc,'vrkr'=>$vrijeme_kraj,'dvo'=>$dvorana,'kom'=>$comment) );
		$id_aktivnosti = $db->lastInsertId();	//ovo je fja iz PDO koja vrati najnoviji id indeksa (autoincrement)
	}
		if($id_aktivnosti===null){ //NIJE REDOVNI - ZNACI DA JE BAS DATUM, NE DAN U TJEDNU
			$st = $db->prepare( 
				'INSERT INTO splanner_azurni_termini (fk_id_redovni_termini,id_grupe_fk, id_trener_fk, datum_origin, vrijeme_poc_stari, vrijeme_kraj_stari, dvorana, comment) VALUES (:id_redovni,:id_grup,:id_tren,:dan,:vrpoc,:vrkr,:dvo,:kom)'
			);
			$dan = date('l', strtotime($datum));
			$st->execute( array( 'id_redovni'=>$id_aktivnosti,'id_grup' => $id, 'id_tren' => $trener, 'dan'=>$datum,'vrpoc'=>$vrijeme_poc,'vrkr'=>$vrijeme_kraj,'dvo'=>$dvorana,'kom'=>$comment) );
		
		}
		else {//REDOVNI JE, ZNACI DA JE DAN U TJEDNU
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
						(fk_id_redovni_termini, id_grupe_fk, id_trener_fk, datum_origin, vrijeme_poc_stari, vrijeme_kraj_stari, dvorana, comment)
						VALUES (:id_redovni, :id_grup, :id_tren, :datum, :vrpoc, :vrkr, :dvo, :kom)'
					);
					$st2->execute([
						'id_redovni' => $id_aktivnosti,
						'id_grup' => $id,
						'id_tren' => $trener,
						'datum' => $date->format('Y-m-d'),
						'vrpoc' => $vrijeme_poc,
						'vrkr' => $vrijeme_kraj,
						'dvo' => $dvorana,
						'kom' => $comment
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


	public function createGrupa($aktivnostId, $ime){
		try
	{
		$db = DB::getConnection();

		$st = $db->prepare( 
			'INSERT INTO splanner_grupe (ime, fk_id_aktivnosti) VALUES (:ime,:aktivnost)'
		);

		$st->execute( array( 'aktivnost' => $aktivnostId, 'ime' => $ime) );
		
	}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
	}

	public function updateAktivnost($id, $ime, $opis, $cijena){
		try
	{
		$db = DB::getConnection();

		$st = $db->prepare( 
			'UPDATE splanner_aktivnosti SET ime=:ime, description=:opis, cijena=:cijena WHERE id_aktivnosti=:id'
		);

		$st->execute( array( 'id' => $id, 'ime' => $ime, 'opis' => $opis, 'cijena' => $cijena ) );
		
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

	public function updateRedovniTermin($id, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare(
				'UPDATE splanner_redovni_termini 
				 SET dan = :datum, vrijeme_poc = :vp, vrijeme_kraj = :vk, dvorana = :dvorana, comment = :comment
				 WHERE id_redovni_termini = :id'
			);
			$st->execute([
				'id' => $id,
				'datum' => $datum,
				'vp' => $vrijeme_poc,
				'vk' => $vrijeme_kraj,
				'dvorana' => $dvorana,
				'comment' => $comment
			]);
		} catch (PDOException $e) {
			throw new Exception("Greška u upisu termina: " . $e->getMessage());
		}
	}

	public function updateAzurniTermin($id, $id_azur, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment,$jelAzurno)
{
	try {
		$db = DB::getConnection();
		if($jelAzurno===1){ //izvanredni termin
		$st = $db->prepare(
			'UPDATE splanner_azurni_termini 
			 SET datum_novi = :datum, vrijeme_poc_novi = :vp, vrijeme_kraj_novi = :vk, dvorana = :dvorana, comment = :comment
			 WHERE id_azurni_termini = :id_az'
		);
	
		$st->execute([
			'id_az' => $id_azur,
			'datum' => $datum,
			'vp' => $vrijeme_poc,
			'vk' => $vrijeme_kraj,
			'dvorana' => $dvorana,
			'comment' => $comment
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
		
			
			$noviDanUTj = strtolower($datum); //
			$noviDatum = clone $stariDatum;
		
			if (strtolower($stariDatum->format('l')) === $noviDanUTj) {
				//ako nije promijenjen dan u tjednu...
			} else {
				$noviDatum->modify('next ' . $noviDanUTj); //inace stavim na prvi sljedeci
			}
		
			
			$st = $db->prepare(
				'UPDATE splanner_azurni_termini 
				 SET datum_origin = :datum, vrijeme_poc_stari = :vp, vrijeme_kraj_stari = :vk, dvorana = :dvorana, comment = :comment
				 WHERE id_azurni_termini = :id_az'
			);
		
			$st->execute([
				'id_az' => $id_azur,
				'datum' => $noviDatum->format('Y-m-d'),
				'vp' => $vrijeme_poc,
				'vk' => $vrijeme_kraj,
				'dvorana' => $dvorana,
				'comment' => $comment
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


		function getAktivnostiForUser($idUser){
			try
		{
			$db = DB::getConnection();
	
			$st = $db->prepare( 
				'SELECT DISTINCT a.*
			 FROM splanner_aktivnosti a
			 JOIN splanner_grupe g ON a.id_aktivnosti = g.fk_id_aktivnosti
			 JOIN veza_je_u v ON g.id_grupe = v.id_grupe_fk
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


    function checkLogin($username, $password)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare('SELECT * FROM ' . self::USERS_TABLE . ' WHERE username=:username');
            $st->execute(['username' => $username]);
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

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

    function getUserIdByName( $username )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT id FROM dz2_users WHERE username=:username' );
			$st->execute( ['username' => $username] );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return $row['id'];
	}
	
	function upisAkt($idTrener,$imeAkt,$descAkt,$cijenaAkt){
		try
	{
		$db = DB::getConnection();

		$st = $db->prepare( 
			'INSERT INTO splanner_aktivnosti (fk_id_trenera, description, cijena, ime) VALUES (:id,:descr,:cijena,:ime)'
		);

		$st->execute( array( 'id' => $idTrener, 'ime' => $imeAkt, 'descr' => $descAkt, 'cijena' => $cijenaAkt ) );
		
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