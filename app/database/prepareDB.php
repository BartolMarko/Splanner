<?php

// Stvaramo tablice u bazi (ako već ne postoje od ranije).
require_once __DIR__ . '/db.class.php';

seed_table_korisnici();

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
		$st = $db->prepare( 'INSERT INTO splanner_korisnici(OIB, username, password_hash, email, tip_korisnika) VALUES (:OIB, :username, :password, \'a@b.com\', :tip)' );

		$st->execute( array( 'OIB' => 123456788901, 'username' => 'mirko', 'password' => password_hash( 'mirkovasifra', PASSWORD_DEFAULT ), 'tip' => 'trener' ) );
		$st->execute( array( 'OIB' => 123456788901, 'username' => 'slavko', 'password' => password_hash( 'slavkovasifra', PASSWORD_DEFAULT ), 'tip' => 'dijete' ) );
		$st->execute( array( 'OIB' => 123456788901, 'username' => 'ana', 'password' => password_hash( 'aninasifra', PASSWORD_DEFAULT ), 'tip' => 'roditelj' ) );
		$st->execute( array( 'OIB' => 123456788901, 'username' => 'maja', 'password' => password_hash( 'majinasifra', PASSWORD_DEFAULT ), 'tip' => 'dijete' ) );
		$st->execute( array( 'OIB' => 123456788901, 'username' => 'pero', 'password' => password_hash( 'perinasifra', PASSWORD_DEFAULT ), 'tip' => 'roditelj' ) );
	}
	catch( PDOException $e ) { exit( "PDO error [insert splanner_korisnici]: " . $e->getMessage() ); }

	echo "Ubacio u tablicu splanner_korisnici.<br />";
}


// // ------------------------------------------
// function seed_table_products()
// {
// 	$db = DB::getConnection();

// 	// Ubaci neke proizvode unutra (ovo nije bas pametno ovako raditi, preko hardcodiranih id-eva usera)
// 	try
// 	{
// 		$st = $db->prepare( 'INSERT INTO dz2_products(id_user, name, description, price) VALUES (:id_user, :name, :description, :price)' );

// 		$st->execute( array( 'id_user' => 1, 'name' => 'Cell Phone Carbon Fiber Soft Cover Case', 'description' => 'Your device will be attractive and usable while protected from scratches in this Stylish New case. Protect your phone from scratches, dust or damages. It moulds perfectly to your phone\'s shape while providing easy access to vital functions.', 'price' => 0.99 ) ); // mirko
// 		$st->execute( array( 'id_user' => 2, 'name' => '50mm Foam Pads Headphone Cover Cap', 'description' => 'Durable and soft The ear foam will enhance the bass performance of your headphones More confortable for your ears.', 'price' => 2.04) ); // slavko
// 		$st->execute( array( 'id_user' => 1, 'name' => 'Phosphor Bronze extra Light Acoustic Guitar Strings', 'description' => 'Lightest gauge of acoustic strings, ideal for beginners or any player that prefers a softer tone and easy bending. Phosphor Bronze was introduced to string making in 1974 and has become synonymous with warm, bright, and well balanced acoustic tone. Phosphor Bronze strings are precision wound with corrosion resistant phosphor bronze onto a carefully drawn, hexagonally shaped, high carbon steel core. The result is long lasting, bright sounding tone with excellent intonation.', 'price' => 7.89 ) ); // mirko
// 		$st->execute( array( 'id_user' => 3, 'name' => '30 Used Tennis Balls - Branded. Very Clean.', 'description' => 'Good condition. All are clean. Branded balls. We have sold over 400,000 balls over a 10 year period so you can be sure of getting a great service and product.', 'price' => 16.89 ) ); // ana
// 	}
// 	catch( PDOException $e ) { exit( "PDO error [dz2_products]: " . $e->getMessage() ); }

// 	echo "Ubacio u tablicu dz2_products.<br />";
// }


// // ------------------------------------------
// function seed_table_sales()
// {
// 	$db = DB::getConnection();

// 	// Ubaci neke prodaje unutra (ovo nije bas pametno ovako raditi, preko hardcodiranih id-eva usera i proizvoda)
// 	try
// 	{
// 		$st = $db->prepare( 'INSERT INTO dz2_sales(id_product, id_user, rating, comment) VALUES (:id_product, :id_user, :rating, :comment)' );

// 		$st->execute( array( 'id_product' => 1, 'id_user' => 4, 'rating' => 5, 'comment' => 'Excellent. Very happy.' ) );
// 		$st->execute( array( 'id_product' => 1, 'id_user' => 5, 'rating' => 3, 'comment' => 'Could be better...' ) );
// 		$st->execute( array( 'id_product' => 1, 'id_user' => 3, 'rating' => NULL, 'comment' => NULL ) );

// 		$st->execute( array( 'id_product' => 2, 'id_user' => 4, 'rating' => 1, 'comment' => 'Don\'t buy. This is a scam.' ) );
// 		$st->execute( array( 'id_product' => 2, 'id_user' => 1, 'rating' => NULL, 'comment' => NULL ) );

// 		$st->execute( array( 'id_product' => 3, 'id_user' => 5, 'rating' => 5, 'comment' => 'Great guitar strings. Would buy again.' ) );
// 		$st->execute( array( 'id_product' => 3, 'id_user' => 3, 'rating' => 4, 'comment' => 'Pretty good strings.' ) );

// 		$st->execute( array( 'id_product' => 4, 'id_user' => 1, 'rating' => 5, 'comment' => 'Great tennis balls, I can now play for the whole year!' ) );
// 	}
// 	catch( PDOException $e ) { exit( "PDO error [dz2_sales]: " . $e->getMessage() ); }

// 	echo "Ubacio u tablicu dz2_sales.<br />";
// }

?> 
