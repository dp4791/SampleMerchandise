<?php
	/* (string $msg) -> void

		Echoes a javascript to alert user with $msg.
	*/
	function alert($msg) {
		echo "<script>alert('".filter_var($msg, FILTER_SANITIZE_STRING)."')</script>";
	}
	
	/* (mixed $v) -> mixed

		Return $v if $v is set, NULL otherwise.
	*/
	function define_var($v) {
		if (isset($v))
			return $v;
		
		return NULL;
	}
	
	/* (string $src, string $dst) -> void

		Copies directory $src to $dst. All nested files/folders are copied as well.
		by gimmicklessgpt@gmail.com (http://php.net/manual/en/function.copy.php#91010)
	*/
	function recurse_copy($src, $dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					recurse_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}
	
	/* (string array $arr) -> string
	
	   Takes array $arr and returns a string in form of '$arr[0], $arr[1], ... , $arr[N]'
	*/
	function array_to_str($arr) {
		$str = "";
		
		foreach ($arr as $item)
			$str .= $item.', ';
		
		// remove last ', '
		$str = rtrim($str, ', '); 
		return $str;
	}
	
	$rootAbsolutePath = $_SERVER["DOCUMENT_ROOT"];
	
	$homeAbsolutePath = realpath(__DIR__.'/../');
	$homeAbsolutePath = str_replace('\\', '/', $homeAbsolutePath);
	
	$homeRelativePath = str_replace($rootAbsolutePath, '', $homeAbsolutePath);
	define("HOME_PATH", $homeRelativePath);
?>