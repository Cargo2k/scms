<?php
class artical {

	public $id;
	public $expireDate;
	public $publishDate;
	public $content;
	public $tags;
	public $title;
	
	public function viewable() {
		$now = time();
		if (isset($this->expireDate))
			$expires = strtotime($this->expireDate);
		else 
			$expires = 0;;
		
		if (isset($this->publishDate))
			$publish = strtotime($this->publishDate);
		else 
			$publish = 0;
		
		if ($now < $expires && $expires) { // the expire date is set, and the expire date is passed
			return false;
		}
		if ($now < $publish || !($publish)) { // no publish date is set, or now is before the publish date
			return false;
		}
		
		return true;
	}
}
 ?>