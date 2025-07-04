<?php

class SplannerService
{
    const USERS_TABLE = 'splanner_korisnici';

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

	function getDjecaByIDRoditelja($IDRod){
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
			$popisDjece[]=[$row['id_korisnici'],$row['ime'],$row['cijena'],$row['description']];
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