<?php
class settings {
// set basic defaults
	// settings that can't be stored in the model, can change based on the model type
	private $viewType_ = 'HTMLScreen';
	private $modelType_ = 'mysql';
	private $modelHost_ = 'localhost';
	private $modelUser_ = 'casey';
	private $modelPass_ = 'nopass';
	private $modelData_ = 'scms';
	
	// values that can be over writen when the model is loaded
	private $language_ = 'en';
	private $model_ = 'mysql';
	private $view_ = 'HTMLScreen';
	private $siteTitle_ = 'Scms default title';
	private $useSiteTitle_ = FALSE;
	private $charset_ = 'http-equiv="content-type" content="text/html" charset="UTF-8"';
	private $doctype_ = 'html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"';
	private $htmltype_ = 'xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"';
	private $userLevel = 'public';
	
	private $rawSettings_ = array();
	
	public function load (&$model) {
		$model->readAllSettings($this->rawSettings_);
	}
	
	// a get function to return the value of the private members
	public function get($varName) {
		if ('_' != substr($varName, -1))
			$varName .= "_";
		if (property_exists('settings', $varName)) {
			return $this->{$varName};
		} else if ($this->rawSettings_["{$varName}"] !== NULL) {
			return $this->rawSettings_["{$varName}"];
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
		} else if ($this->rawSettings_["{$varName}"] !== NULL) {
			$this->rawSettings_["{$varName}"] = $value;
			return $this->rawSettings_["{$varName}"];
		}
		
		return -1;
	}
}
?>