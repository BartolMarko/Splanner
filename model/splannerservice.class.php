<?php

require_once __DIR__ . '/../app/database/db.class.php';

class SplannerService
{
    const USERS_TABLE = 'splanner_korisnici';

	public function updateRedovniTermin($id, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment)
	{
		try {
			$db = DB::getConnection();
			$st = $db->prepare(
				'UPDATE splanner_redovni_termini 
				 SET datum = :datum, vrijeme_poc = :vp, vrijeme_kraj = :vk, dvorana = :dvorana, comment = :comment
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

	public function updateAzurniTermin($id, $datum, $vrijeme_poc, $vrijeme_kraj, $dvorana, $comment)
{
	try {
		$db = DB::getConnection();
		$st = $db->prepare(
			'UPDATE splanner_azurni_termini 
			 SET datum = :datum, vrijeme_poc = :vp, vrijeme_kraj = :vk, dvorana = :dvorana, comment = :comment
			 WHERE id_azurni_termini = :id'
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