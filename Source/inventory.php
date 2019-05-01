<?php 
	include_once("lib/common.php");
	include_once("lib/sql.php");
	include_once("lib/database.php");
	include_once("lib/inventory.php");
	
	$DEFAULT_IMG_PATH = constant('HOME_PATH').'/Image/NoImage.png';
	
	session_start();
?>
<!DOCTYPE html>
<html>
<?php
	if (isset($_POST['submit'])) {
		$productID = $_POST['productID'];
		
		if (!isset($_SESSION['purchaseCount']))
			$_SESSION['purchaseCount'] = array();
		
		if ($_POST['count'] > 0) {
			if (!isset($_SESSION['purchaseCount'][$productID]))
				$_SESSION['purchaseCount'][$productID] = 0;
			
			$_SESSION['purchaseCount'][$productID] += $_POST['count'];
		}
	}
?>
<?php
	$inventory = new Inventory();
	
	try {
		// retrieve category
		$categoryID = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_NUMBER_INT);
		
		if (empty($categoryID))
			throw new Exception("Invalid category");
		else
			$categoryID = intval($categoryID);
		
		$stmt = $db->prepare("SELECT * FROM category WHERE ID=:ID");
		$stmt->bindValue(':ID', $categoryID, SQLITE3_INTEGER);
		$result = $stmt->execute();
		
		if (!($category = $result->fetchArray()))
			throw new Exception("Category does not exist");
		
		// obtain list of matching products and build inventory
		$queryString = "SELECT * FROM product WHERE category_ID=:category_ID";
		
		$searchFilter = filter_input(INPUT_POST, 'searchFilter', FILTER_SANITIZE_STRING);
		if (!empty($searchFilter)) {
			// filter may match any portion of text
			$searchFilter = "%".$searchFilter."%";
			
			// add onto SQL query
			$queryString .= " AND (name LIKE :filter OR description LIKE :filter)";
		}
		
		$stmt = $db->prepare($queryString);
		$stmt->bindValue(':category_ID', $categoryID, SQLITE3_INTEGER);
		
		if (!empty($searchFilter))
			$stmt->bindValue(':filter', $searchFilter, SQLITE3_TEXT);
		
		$products = $stmt->execute();
		while ($product = $products->fetchArray()) {
			$item = Item::createFromArray($product);
			$inventory->addItem($item);
		}
	} catch (Exception $e) {
	}
?>

<head>
  <title><?php echo $category['name']; ?> | Good Merchandise</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<?php 
	include "Base/header.php";
?>
  <div id="contents" class="clearfix">
    <form id="search-bar" method="post">
      <input type="text" name="searchFilter" placeholder="Search here" autocomplete="off">
    </form>
	<ul id="inventory" class="left clearfix">
<?php
	$items = $inventory->getItems();
	
	if (!empty($items)) {
		foreach ($items as $item) {
			$unit = $item->getUnit();
?>
	  <li class="left">
	    <div class="img-box">
		  <!-- Replace image with default image if error occurred (failed to load/image doesn't exist); 
			   set onerror to null so that it does not loop forever -->
		  <img src="<?php echo $item->getImagePath(); ?>" onerror="this.onerror=''; this.src='<?php echo $DEFAULT_IMG_PATH; ?>'">
		</div>
		<div class="text-box">
<?php 
			echo $item->getFormattedName();
?>
		  <br>
		  Price: <?php echo $item->getFormattedPrice(); ?><br>
		  <form method="post" class="inline-block">
		    <input type="hidden" name="productID" value="<?php echo $item->getID(); ?>">
			<input type="text" name="count" value="1" size="1" maxlength="2">
			<input type="submit" name="submit" value="Add to Cart">
		  </form>
		</div>
		<div class="tooltip">
<?php 
			echo $item->getFormattedDescription();
			echo '<br><br>';
			
			if (!empty($_SESSION['purchaseCount'][$item->getID()]))
				echo 'You already have <b>'.$_SESSION['purchaseCount'][$item->getID()].'</b> in cart';
?>
		</div>
	  </li>
<?php
		}
	}
?>
	</ul>
  </div>
<?php
	include "Base/footer.php";
?>
</body>

</html>