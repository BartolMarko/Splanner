<?php 

class _404Controller extends BaseController
{
	public function index() 
	{
		// Popuni template potrebnim podacima
		$this->registry->template->title = '404 - Not Found';

        $this->registry->template->show( '404_index' );
	}
}; 

?>
