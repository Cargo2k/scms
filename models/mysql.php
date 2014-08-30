<?php
// needs the view defined before using for debug purposes
class model {
// Opens a connection to a mysql database
	public function open($hostName = 0, $userName = 0, $password = 0, $database = 0, $port = 0, $socket = 0) {		
		$this->db_ = new mysqli($hostName, $userName, $password, $database, $port, $socket);
		if ($this->db_->connect_error) {
			return $this->dbError_();
		}
		
		return 0;
	}

// a funtion for raw select statments 
	public function read() {
		global $view;
		$view->addStringNL("mysql: Reading not implimented");
	}

// closes the mysqli connection
	public function close() {
		$this->db_->close();
	}

// returns a string based off the last error
	public function errorString () {
		return "Error #: " . $this->errno_ . " Message: " . $this->errmsg_;
	}
	
// returns the last error number
	public function errno() {
		return $this->errno_;		
	}
	
// returns the last error message
	public function errmsg() {
		return $this->errmsg_;
	}
	
	// handles error from the db
	private function dbError_() {
		$this->errno_ = $this->db_->errno;
		$this->errmsg_ = $this->db_->error;
		return $this->errno_;
	}
	
// sets a supplied error, returns the errno_
	private function setDbError_($errno, $errmsg) {
		$this->errno_ = $errno;
		$this->errmsg_ = $errmsg;
		return $this->errno_;
	}
	
// ****** functions for settings

// writes a setting key value pair for this model type
	public function writeSetting($key, $value) {
		if ($statement = $this->db_->prepare("INSERT INTO settings (setting, data) 
											VALUES (?, ?) 
											ON DUPLICATE KEY UPDATE
												setting=VALUES(setting), 
												data=VALUES(data)")) {
			$statement->bind_param('ss', $key, $value);
			$statement->execute();
			if ($this->db_->errno)
			{
				$statement->close();
				return $this->dbError_();
			}
		} else {
			if ($this->db_->errno)
			{
				return $this->dbError_();
			} else { 
				return $this->setDbError_(-1, "Unknown Error");
			}
		}
		return 0;
	}
	
// reads all the settings from the settings table
	public function readAllSettings(&$settings)
	{
		if ($statement = $this->db_->prepare("SELECT setting, data 
											FROM settings")) {
			$statement->execute();
			if ($this->db_->errno)
			{
				$statement->close();
				return $this->dbError_();
			}
			$results = $statement->get_result();
			$buffer = $results->fetch_all();
			foreach ($buffer as $key => $row) {
				$settings["{$row[0]}"] = $row[1];
			}
			
		} else {
			return $this->dbError_();		
		}
	}
	
// reads a setting for a key, returns value and writes to the $value if provided
	public function readSetting($key, &$value = NULL) {
		$retVal = 'primed';
		if ($statement = $this->db_->prepare("SELECT data 
											FROM settings 
											WHERE setting = ?")) {
			$statement->bind_param('s', $key);
			$statement->execute();
			if ($this->db_->errno)
			{
				$statement->close();
				return $this->dbError_();
			}
			$statement->bind_result($retVal);
			$statement->fetch();
			$statement->close();
		} else {
			if ($this->db_->errno)
			{
				return $this->dbError_();
			} else { 
				return $this->setDbError_(-1, "Unknown Error");
			}
		}
		if ($value !== NULL)
			$value = $retVal;
		return $retVal;
	}
// ***** end of functions for settings

// ***** functions for menus
	// reads $recursionLevel levels of the menu $menuId into $menuData
	// if $recursionLevel == 0 the whole menu will be loaded
	public function readMenu(&$menuData, $menuId, $recursionLevel = 0) {
		$level = 0;
		// read the menu root
		if ($statement = $this->db_->prepare("SELECT * 
											FROM menuNodes 
											WHERE id = ?")) {
			$newNode = new menuNode;
			$statement->bind_param('i', $menuId);
			$statement->execute();
			if ($this->db_->errno)
			{
				$statement->close();
				return $this->dbError_();
			}
			if ($results = $statement->get_result()) {
				$buffer = $results->fetch_assoc();
				$newNode->id = $buffer['id'];
				$newNode->parentId = $buffer['parentId'];
				$newNode->name = $buffer['name'];
				$newNode->label = $buffer['label'];
				$newNode->type = $buffer['type'];
				array_push($menuData, $newNode); // store the root menu
			} else {
				return $this->setDbError_(-2, "Root menu id {$menuId} was not found");
			}
		} else {
			return $this->dbError_();
		}

		++$level;
		if ($level != $recursionLevel) { //check recursion level
			if ($statement = $this->db_->prepare("SELECT * 
											FROM menuNodes
											WHERE parentId = ?
											ORDER BY id")) { // prep the select for the child menus
				if ($this->readChildMenus_($menuData, $menuId, $recursionLevel, $level, $statement))
				{
					$statement->close();
					return $this->errno_; // pass the error back
				}
			} else {
				$statement->close();
				return $this->dbError_();
			}
			$statement->close();
		}
		
		return 0;
	}
	
	private function readChildMenus_(&$menuData, $parentId, $recursionLevel, &$level, &$statement) {
			if ($level == $recursionLevel)
				return 0;
			if (!$statement->bind_param('i', $parentId))
				return $this->dbError_();
			if (!$statement->execute())
				return $this->dbError_();
			if ($results = $statement->get_result()) {
				while ($buffer = $results->fetch_assoc()) {
					$newNode = new menuNode;
					$newNode->id = $buffer['id'];
					$newNode->parentId = $buffer['parentId'];
					$newNode->name = $buffer['name'];
					$newNode->label = $buffer['label'];
					$newNode->type = $buffer['type'];
					array_push($menuData, $newNode);
					++$level;
					$this->readChildMenus_($menuData, $newNode->id, $recursionLevel, $level, $statement);
					--$level;
				}
			}
		return 0;
	}
	
	public function readLeafs(&$menuData) {
		if ($statement = $this->db_->prepare("SELECT * FROM menuLeafs 
												WHERE parentId = ?
												ORDER BY id")) {
			foreach ($menuData as $key=>&$menu) {
				if (!$statement->bind_param('i', $menu->id))
					return $this->dbError_();
				if (!$statement->execute())
					return $this->dbError_();
				if ($results = $statement->get_result()) {
					while ($buffer = $results->fetch_assoc()) {						
						$newLeaf = new menuLeaf;
						$newLeaf->id = $buffer['id'];
						$newLeaf->parentId = $buffer['parentId'];
						$newLeaf->expireDate = $buffer['expireDate'];
						$newLeaf->publishDate = $buffer['publishDate'];
						$newLeaf->title = $buffer['title'];
						
						array_push($menu->leafs, $newLeaf);
					}
				}
			}
		} else {
			$statement->close();
			return $this->dbError_();
		}
	}
// ***** end of functions for menus

// ***** begin functions for articals
// read a function of of the database
	public function readArtical($articalId, &$artical) {
		if (!$articalId)
			return $this->setDbError_(-1, "Invalid artical ID");
		if ($statement = $this->db_->prepare("SELECT * FROM menuLeafs
												WHERE id = ?")) {
			if (!$statement->bind_param('i', $articalId))
				return $this->dbError_();
			if (!$statement->execute())
				return $this->dbError_();
			if ($results = $statement->get_result()) {
				if ($buffer = $results->fetch_assoc()) {
					$artical->id = $buffer['id'];
					$artical->expireDate = $buffer['expireDate'];
					$artical->publishDate = $buffer['publishDate'];
					$artical->content = $buffer['content'];
					$artical->tags = $buffer['tags'];
					$artical->title = $buffer['title'];
				} else {
					// no match
					return $this->dbError_();
				}
			}
		} else {
			$statement->close();
			return $this->dbError_();
		}
	}
// ***** end of functions for articals
// private data members
	private $errno_ = 0;
	private $errmsg_ = '';
	private $db_;
}

?>