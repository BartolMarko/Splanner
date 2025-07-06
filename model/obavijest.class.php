<?php

class Obavijest
{
	protected $id_obavijest, $id_grupe_fk, $datum, $vrijeme, $comment;

	function __construct( $id_obavijest, $id_grupe_fk, $ime, $datum, $vrijeme, $comment )
	{
		$this->id_obavijest = $id_obavijest;
		$this->id_grupe_fk = $id_grupe_fk;
		$this->ime = $ime;
		$this->datum = $datum;
		$this->vrijeme = $vrijeme;
		$this->comment = $comment;
	}

	function __get( $prop ) { return $this->$prop; }
	function __set( $prop, $val ) { $this->$prop = $val; return $this; }
}

?>