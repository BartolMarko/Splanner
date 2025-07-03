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
            return false; 
        if (password_verify($password, $row['password_hash'])) {
            return true; 
        } else {
            return false;
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