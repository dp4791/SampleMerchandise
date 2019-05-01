<?php
	function sql_to_text($var) {
		if ($var == NULL)
			return 'NULL';
			
		return "'".$var."'";
	}
	
	function sql_to_number($var) {
		if ($var == NULL)
			return 'NULL';
		
		return $var;
	}
	
	function sql_is_result_empty($result) {
		$result->reset();
		
		if (!$result->fetchArray())
			return true;
		
		$result->reset();
		return false;
	}
	
	/* ($result = SQLite3Result containing columns (ID, name) and records)
		
		Echoes select options that displays each record in $result,
		each entry showing the record's name and having value equal to the record's ID.
	*/
	function sql_echo_result_as_options($result, $selectedID = -1) {
		$result->reset();
		
		while ($data = $result->fetchArray()) {
			$ID = $data['ID'];
			$name = $data['name'];
			
			if ($selected == $ID)
				echo "<option value=\"$ID\" selected=\"selected\">";
			else
				echo "<option value=\"$ID\">";
			
			echo "$name";
			echo '</option>';
		}
		
		$result->reset();
	}
?> 