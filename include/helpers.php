<?php
/*
 * depends on http://pecl.php.net/package/operator writen against 0.4.1 two usage types attached to an array buffer: echo "<pre>"; $buffer = array(); $nest = new nester($buffer); $nest++; ++$nest; $nest++; echo "\n'" . $nest->nest() . "nested string'\n"; var_dump($nest); $nest--; --$nest; $nest--; var_dump($nest); var_dump($buffer); echo "</pre>"; or independently: echo "<pre>"; $nest = new nester(); echo $nest++ . "\n"; echo ++$nest . "\n"; echo $nest++ . "\n"; echo "\n'" . $nest->nest() . "nested string'\n"; var_dump($nest); echo $nest-- . "\n"; echo --$nest . "\n"; echo $nest-- . "\n"; var_dump($nest); echo "</pre>";
 */
class nester {
	private $value = 0;
	private $nestChar = '  ';
	private $nestString = 'NESTLEVEL';
	private $delimit = ':';
	private $buffer = array();
	private $bufferFlag = FALSE;
	
	public function __construct(&$bufferIn = NULL) {
		if ($bufferIn) {
			if (gettype ( $bufferIn != "array" )) {
				// signal error
			}
		}
		if (gettype ( $bufferIn ) == "array") {
			$this->buffer = & $bufferIn;
			$this->bufferFlag = TRUE;
		}
	}
	
	private function Nest_() {
		return "{$this->nestString}{$this->delimit}{$this->value}";
	}
	
	public function setBuffer(&$newBuffer) {
		array_unshift($this->buffer, $newBuffer);
	}
	
	public function unsetBuffer() {
		array_shift($this->buffer);
	}
	
	public function nest($nestString = -1) {
		$retVal = '';
		$used = 0;
		switch (gettype ( $nestString )) {
			case ("string") :
				$data = explode ( $this->delimit, $nestString );
				$used = $data [1];
				break;
			case ("double") :
			case ("integer") :
			case ("float") :
				if ($nestString == - 1) // default usage
					$used = &$this->value;
				else
					$used = &$nestString;
				break;
			default :
				// prolly should throw an exception
				return 0;
		}
		for($i = 0; $i < $used; $i ++)
			$retVal .= $this->nestChar;
		return $retVal;
	}
	
	function __post_inc() {
		global $view;
		++ $this->value;
		if ($this->bufferFlag)
			array_push ( $this->buffer, $this->Nest_ () );
		return $this->Nest_ ();
	}
	
	function __post_dec() {
		global $view;
		-- $this->value;
		if ($this->bufferFlag)
			array_push ( $this->buffer, $this->Nest_ () );
		return $this->Nest_ ();
	}
	
	function __pre_inc() {
		global $view;
		++ $this->value;
		if ($this->bufferFlag)
			array_push ( $this->buffer, $this->Nest_ () );
		return $this->Nest_ ();
	}
	
	function __pre_dec() {
		global $view;
		-- $this->value;
		if ($this->bufferFlag)
			array_push ( $this->buffer, $this->Nest_ () );
		return $this->Nest_ ();
	}
}
?>