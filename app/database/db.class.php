<?php

// Datoteku treba preimenovati u db.class.php

class DB
{
	private static $db = null;

	private function __construct() { }
	private function __clone() { }

	public static function getConnection() 
	{
		if( DB::$db === null )
	    {
	    	try
	    	{
				// $student_username_file = file(__SITE_PATH . "/student_username.txt", FILE_IGNORE_NEW_LINES);
				// $student_username = $student_username_file[0];
				// $db_name = $student_username_file[1];
		    	DB::$db = new PDO(
					"mysql:host=rp2.studenti.math.hr;dbname=rizner;charset=utf8", 'student', "pass.mysql"
				);
		    	DB::$db-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    }
		    catch( PDOException $e ) { exit( 'PDO Error: ' . $e->getMessage() ); }
	    }
		return DB::$db;
	}
}

?>

