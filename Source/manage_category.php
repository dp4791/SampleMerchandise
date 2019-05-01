<?php
	include_once("lib/common.php");
	include_once("lib/sql.php");
	include_once("lib/database.php");
?>
<!DOCTYPE html>
<html>
<?php
	if (isset($_POST['create'])) {
		$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
		$parent = filter_input(INPUT_POST, 'parent', FILTER_SANITIZE_NUMBER_INT);
		
		$db->create_category($name, $parent);
	}
	
	if (isset($_POST['delete'])) {
		$ID = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);
		
		$db->delete_category($ID);
	}
?>

<head>
  <title>Category Manager | Good Merchandise</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<?php 
	include "Base/header.php";
?>
  <fieldset>
    <legend>Create category</legend>
    <form method="post">
	  <div class="field">
	    <label for="name">Name</label>
	    <input type="text" name="name">
	  </div>
	  <div class="field">
	    <label for="parent">Under category</label>
		<select name="parent">
		  <option value="0">--</option>
<?php
	// add supercategories to options
	$supercats = $db->select_supercategories();
	sql_echo_result_as_options($supercats);
?>
        </select>
	  </div>
	  <br><input type="submit" name="create" value="Create">
    </form>
  </fieldset>
  <fieldset>
    <legend>Delete category</legend>
    <form method="post">
	  <div class="field">
	    <label for="ID">Category</label>
		<select name="ID">
<?php
	// add supercategories to options
	$supercats = $db->select_supercategories();
	sql_echo_result_as_options($supercats);
	
	// loop through each supercategories
	while ($supercat = $supercats->fetchArray()) {
		$supercatName = $supercat['name'];
		$supercatID = $supercat['ID'];
		
		// add its subcategories as options under its heading
		$subcats = $db->select_subcategories($supercatID);
		if (!sql_is_result_empty($subcats)) {
			echo "<optgroup label=\"$supercatName\">";
			sql_echo_result_as_options($subcats);
			echo '</optgroup>';
		}
	}
?>
        </select>
      </div>
	  <br><input type="submit" name="delete" value="Delete">
    </form>
  </fieldset>
<?php
	include "Base/footer.php";
?>
</body>

</html>