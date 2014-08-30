<?php
#include "include/helpers.php"

class htmlMenuTags {
	public $cOpen = '';
	public $cClose = '';
	public $rOpen = '';
	public $rClose = '';
	public $nOpen = '';
	public $nClose = '';
	public $lOpen = '';
	public $lClose = '';
}

class view {
// the raw string of the body of the html document
	private $body_ = array();
	private $cssById_ = array();
	private $cssByClass_ = array();
	private $debug_ = array();
	
	private $charset_ = '';
	private $doctype_ = '';
	private $htmltype_ = '';
	private $siteTitle_ = '';
	private $useSiteTitle_ = '';
	private $language_ = '';
	
	private $menus_ = array();
	
	private $nest; // = new nester;
	
// defualt constructor
	public function __construct(&$settings = NULL) {
		$this->nest = new nester();
		
		if ($settings == NULL)
			return;	
		
		$this->charset_ = $settings->get('charset');
		$this->siteTitle_ = $settings->get('siteTitle');
		$this->language_ = $settings->get('language');
		$this->doctype_ = $settings->get('doctype');
		$this->htmltype_ = $settings->get('htmltype');	
		$this->useSiteTitle_ = $settings->get('useSiteTitle');			
	}
	
// adds a string to a buffer, defaults to the body of the html document	
	public function addString($newHTML, &$buffer = -1) {
		if ($buffer == -1)
			$buffer = &$this->body_;
		$lines = explode("\n", $newHTML);
		foreach ($lines as &$line)
			array_push($buffer, $this->nest->nest() . $line);
	}

// adds a <br \>\n the the document
	public function addNL(&$buffer = -1) {
		if ($buffer == -1)
			$buffer = &$this->body;
		array_push($this->body_, "<br />\n");
	}
	
// adds a string and line ending to the body of the html document	
	public function addStringNL($newHTML, &$buffer = -1) {
		$this->addString($newHTML, $buffer);
		$this->addNL($buffer);
	}
	
// loads the tags needed for the specified menu type
	private function buildMenuTags_($menuType, $menuId) {
		// add the ability to load more then the default menu type
		$retVal = new htmlMenuTags();

		$retVal->cOpen = '<ul>';
		$retVal->cClose = '</ul>';
		$retVal->rOpen = '<div id="menuContainer"><ul id="nav" class="drop">';
		$retVal->rClose = '</ul></div>';
		$retVal->nOpen = '<li>';
		$retVal->nClose = '</li>';
		$retVal->lOpen = '<li><a href="compose">';
		$retVal->lClose = '</a></li>';
		
		array_push($this->cssById_, 'body {background: #e8e8e8; margin: 0; padding: 0; font-family: arial; font-size: 14px;}');
//		array_push($this->cssById_, '#menuContainer {width: 900px; height: 200px; margin: 20px auto; border: 3px dashed #cecece; }');
		array_push($this->cssById_, 'ul#nav {margin: 0 0 0 200px;}');
		
		array_push($this->cssByClass_, 'ul.drop a { display:block; color: #fff; font-family: arial; font-size: 14px; text-decoration: none;}');
		array_push($this->cssByClass_, 'ul.drop, ul.drop li, ul.drop ul { list-style: none; margin: 0; padding: 0; border: 1px solid #fff; background: #555; color: #fff;}');
		array_push($this->cssByClass_, 'ul.drop { position: relative; z-index: 597; float: left; }');
		array_push($this->cssByClass_, 'ul.drop li { float: left; line-height: 1.3em; vertical-align: middle; zoom: 1; padding: 5px 10px; }');
		array_push($this->cssByClass_, 'ul.drop li.hover, ul.drop li:hover { position: relative; z-index: 599; cursor: default; background: #1e7c9a; }');
		array_push($this->cssByClass_, 'ul.drop ul { visibility: hidden; position: absolute; top: 100%; left: 0; z-index: 598; width: 195px; background: #555; border: 1px solid #fff; }');
		array_push($this->cssByClass_, 'ul.drop ul li { float: none; }');
		array_push($this->cssByClass_, 'ul.drop ul ul { top: -2px; left: 100%; }');
		array_push($this->cssByClass_, 'ul.drop li:hover > ul { visibility: visible }');

		return $retVal;
	}
	
// adds a menu to the document
	public function addMenu($menuData, $menuType = 0) {
		$tags = $this->buildMenuTags_($menuType, $menuData[0]->name); 
		$parents[] = array(); // a stack to store the nested items parent ids
		$first = true; // a flag to tell if not the first element 
		$prevId = 0; // the previous items id to check for a new nesting
		$menuBuffer = array();  // the actual menu buffer to be returned
		$this->nest->setBuffer($menuBuffer);
//		$menuString = $this->nest_->nest() . $tags->rOpen . "\n"; // the actual menu string to be returned
		
		$this->addString($tags->rOpen, $menuBuffer);
		array_unshift($parents, $menuData[0]->id);
		$this->nest++;
		for ($i = 1; $i <count($menuData); ++$i) {
			$menu = &$menuData[$i];
			if ($menu->parentId == 0) { // dummy past for the root element, and store its id
				array_unshift($parents, $menu->parentId);
			} else if ($menu->parentId != $parents[0]) { 
				 if ($menu->parentId == $prevId) {
//					$menuString .= $this->nest() . $tags->cOpen . "\n"; // menus parent is the prev element start a new sub list
					$this->addString($tags->cOpen, $menuBuffer);
					$this->nest++;
					array_unshift($parents, $menu->parentId);
				} else { // menus perant is a level above, close out the lists in between
//					$menuString .= $this->nest() . $tags->nClose . "\n";
					$this->addString($tags->nClose, $menuBuffer);
					while ($menu->parentId != $parents[0] && $parents) {
						$this->nest--;
//						$menuString .= $this->nest() . $tags->cClose . "\n" . $this->nest() . $tags->nClose . "\n";
						$this->addString($tags->cClose, $menuBuffer);
						$this->nest--;
						$this->addString($tags->nClose, $menuBuffer);
						array_shift($parents);
					}
				}
			} else if (!$first) {
//				$menuString .= $this->nest() . $tags->nClose . "\n";
				$this->nest--;
				$this->addString($tags->nClose, $menuBuffer);
			}
//			$menuString .= $this->nest() . $tags->nOpen . "{$menu->label}\n"; // add this element
			$this->addString($tags->nOpen . $menu->label, $menuBuffer);
			$this->nest++;
			// look for any leafs attached and add
			if ($menu->leafs) {
//				$menuString .=  $this->nest() . $tags->cOpen . "\n";
				$this->addString($tags->cOpen, $menuBuffer);
				$this->nest++;
				foreach ($menu->leafs as &$node) {
					$lOpen = '';
					if ($pos = strpos($tags->lOpen, 'compose')) { // check for a url that needs composed 
						$len = strlen($tags->lOpen);
						for ($j = 0; $j < $len; ++$j) {
							if ($j == $pos) {
								$j += strlen('compose');
								$lOpen .= $this->composeArticalURL_($node->id); // add the composed url here
							}
							$lOpen .= $tags->lOpen[$j];
						}
					} else {
						$lOpen = $tags->lOpen;
					}
//					$menuString .= $this->nest() . $lOpen . $node->title . $tags->lClose . "\n";
					$this->addString($lOpen . $node->title . $tags->lClose, $menuBuffer);
				}
				$this->nest--;
//				$menuString .=  $this->nest() . $tags->cClose . "\n";
				$this->addString($tags->cClose, $menuBuffer);
			}
			$first = false;
			$prevId = $menu->id;
		}
		$count = count($parents) - 1;
		while ($count) { // close any list levels not closed by the last element 
//			$menuString .= $this->nest() . $tags->nClose . "\n";
			$this->addString($tags->nClose, $menuBuffer);
			$this->nest--;
			if ($count < 1) // we need to separate the closing of the root tag 
//				$menuString .=  $this->nest() . $tags->cClose . "\n";
				$this->addString($tags->cClose, $menuBuffer);
			array_shift($parents);
			--$count;
		}
//		$menuString .=  $this->nest() . $tags->rClose . "\n";
		$this->addString($tags->rClose, $menuBuffer);
		$this->nest--;
//		array_push($this->menus_, $menuString);
		array_push($this->menus_, $menuBuffer);
		$this->nest->unSetBuffer();
		
		return 0;
	}
	
	private function composeArticalURL_($articalId) {
		return "index.php?viewArtical={$articalId}";
	}
	
	public function addArtical ($artical) {
		if (!$this->useSiteTitle_)
			$this->siteTitle_ = $artical->title;
		
		$this->addStringNL('<div id="artical">');
		$this->addStringNL('<p>');
		$this->addStringNL($artical->content);
		$this->addStringNL('</p>');
		$this->addStringNL('</div>');
	}
	
// prints the built document
	public function display() {
		print "<!DOCTYPE {$this->doctype_} />\n";
		print "<HTML {$this->htmltype_}>\n";
		print "<HEAD>\n";
		print "  <META {$this->charset_}/>\n";
		print "  <TITLE>\n";
		print "    {$this->siteTitle_}\n";
		print "  </TITLE>\n";
		print "  <STYLE>\n";
		for ($i = 0; $i < count($this->cssById_); ++$i) {
			print "    {$this->cssById_[$i]}\n";
		}
		for ($i = 0; $i < count($this->cssByClass_); ++$i) {
			print "    {$this->cssByClass_[$i]}\n";
		}
		print "  </STYLE>\n";
		print "</HEAD>\n";
		print "<BODY>\n";
		if ($this->debug_) {
			print '<div id="debugMsg"><pre>' . "\n";
			print $this->debugMsg();
			print '</pre></div id="debugMsg">';
		}
		foreach ($this->menus_ as &$menu) {
			foreach ($menu as &$menuLine) {
				print $menuLine . "\n";
			}
		}
		foreach ($this->body_ as &$bodyLine) {
			print $bodyLine . "\n";
		}
		print "</BODY>\n";
		print "</HTML>\n";
	} 
	
// prints the message argument and kills execution
	public function fatal($message) {
		print "<!DOCTYPE html />\n";
		print "<HTML>\n";
		print "<BODY>\n";
		print $message;
		print "\n</BODY>\n";
		print "</HTML>\n";
		flush();
		die();
	}
	
// add a mesage to the debug div
	public function addDebugMsg($string) {
		array_push($this->debug_, $string);
	}
	
// add var dump to the debug section
	public function varDump($var) {
		$string = var_export($var, TRUE);
		$this->addDebugMsg($string);
	}
	
// returns a string built of the debug messages
	public function debugMsg() {
		$retVal = '';
		foreach ($this->debug_ as &$line) {
			$retVal .= $line . "\n";
		}
		return $retVal;
	}
	
// a get function to return the value of the private members
	public function get($varName) {
		if ('_' != substr($varName, -1))
			$varName .= "_";
		if (property_exists('view', $varName)) {
			return $temp->{$varName};
		}
		return NULL;
	}
	
// a set function to change the private members
	public function set($varName, $value) {
		if ('_' != substr($varName, -1))
			$varName .= "_";
		if (property_exists('view', $varName)) {
			$this->{$varName} = $value;
			return $this->{$varName};
		}
		return -1;
	}
}