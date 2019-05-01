<?php
	include_once(__DIR__."/../lib/sql.php");
	include_once(__DIR__."/../lib/database.php");
?>

<div id="header">
  <a id="title" href="home.php">Good Merchandise</a>
  <a id="cart" href="cart.php">My Cart</a>
</div>

<div id="nav">
  <ul class="clearfix">
<?php
	$supercats = $db->select_supercategories();
	while ($supercat = $supercats->fetchArray()) {
		$supercatID = $supercat['ID'];
		$supercatName = $supercat['name'];
		
		echo '<li class="left">';
		echo "<a>$supercatName</a>";
		
		$subcats = $db->select_subcategories($supercatID);
		if (!sql_is_result_empty($subcats)) {
			echo '<ul class="blanklist dropdown">';
			while ($subcat = $subcats->fetchArray()) {
				$subcatID = $subcat['ID'];
				$subcatName = $subcat['name'];
				
				echo "<li><a href=\"inventory.php?category=$subcatID\">$subcatName</a></li>";
			}
			echo '</ul>';
		}
		
		echo '</li>';
	}
?>
    <li id="about" class="right">
	  <a href="about.php">About</a>
    </li>
  </ul>
</div>
