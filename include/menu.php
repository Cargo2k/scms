<?php
class menuNode {
	public $id;
	public $parentId;
	public $name;
	public $label;
	public $type;
	public $leafs = array();
}

class menuLeaf {
	public $id;
	public $parentId;
	public $expireDate;
	public $publishDate;
	public $title;
}

class menu {
	private $rawElements_ = array();
	private $preferedDisplay_;
	private $type_;
	
	public function __construct($id, $model, $readLeafs = true) {	
		if (!isset($id)) {
			return -1;	
		}
		if ($model->readMenu($this->rawElements_, $id)) { // read in the branch structure of the menu
			global $view;
			$view->fatal($model->errorString());
		}
		if ($readLeafs) {
			if ($model->readLeafs($this->rawElements_)) { // read in the leafs for the existing branches
				global $view;
				$view->fatal($model->errorString());
			}
		}
	}
	
	public function addToView($view) {
		$view->addMenu($this->rawElements_);
	}
	
	// a get function to return the value of the private members
	public function get($varName) {
		if ('_' != substr($varName, -1))
			$varName .= "_";
		if (property_exists('settings', $varName)) {
			return $this->{$varName};
		} 
	
		return NULL;
	}
	
	// a set function to change the private members
	public function set($varName, $value) {
		if ('_' != substr($varName, -1))
			$varName .= "_";
		if (property_exists('settings', $varName)) {
			$this->{$varName} = $value;
			return $this->{$varName};
		} 
	
		return -1;
	}
}
 ?>