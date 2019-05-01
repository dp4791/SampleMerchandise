<?php
	include_once("lib/common.php");
	include_once("lib/sql.php");
	include_once("lib/database.php");
	
	$DEFAULT_IMAGE_FOLDER = "Image/Product/";
	$ALLOWED_IMAGE_TYPES = array(
		"jpg",
		"jpeg",
		"png"
	);
	$FILTERS = array(
		'name'		 	=> FILTER_SANITIZE_STRING,
		'categoryID' 	=> FILTER_SANITIZE_NUMBER_INT,
		'description' 	=> FILTER_SANITIZE_STRING,
		
		// regexp for float (ex. 2, 2., 2.33)
		'price' 		=> array('filter' 	=> FILTER_VALIDATE_REGEXP,
								 'options'	=> array('regexp' => '/^[0-9]+(\.[0-9]*)?$/')
								),
		
		'unit' 			=> FILTER_SANITIZE_STRING,
	);
?>
<!DOCTYPE html>
<html>
<?php
	if (isset($_POST['create'])) {
		try {
			$inputs = filter_input_array(INPUT_POST, $FILTERS);
				
			$name = $inputs['name'];
			$categoryID = intval($inputs['categoryID']);
			$description = $inputs['description'];
			$price = floatval($inputs['price']);
			$unit = $inputs['unit'];
			$imagePath = NULL;
			
			// see if we have an image
			if (!empty($_FILES['image']['name'])) {
				$image = $_FILES['image'];
				
				$fileName = $image['name'];
				$fileExt = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
				
				// security checks
				if (!in_array($fileExt, $ALLOWED_IMAGE_TYPES))
					throw new Exception("You can only upload ".array_to_str($ALLOWED_IMAGE_TYPES));
					
				// gets next ID in product table (this + 1 = assigned to the next product)
				$nextID = intval($db->query("SELECT * FROM SQLITE_SEQUENCE WHERE name='product'")->fetchArray()['seq']) + 1;
				
				// Apple.jpg -> (ImageFolderPath)/(ID)_Apple.jpg
				$imagePath = $DEFAULT_IMAGE_FOLDER.$nextID.'_'.$fileName;
			}
			
			$db->create_product($name, $categoryID, $description, $price, $unit, $imagePath);
			
			if (!empty($image))
				move_uploaded_file($image['tmp_name'], $imagePath);
			
			alert('Product was added successfully!');
		} catch (Exception $e) {
			alert($e->getMessage());
		}
	}
	
	if (isset($_POST['delete'])) {
		$productID = intval(filter_input(INPUT_POST, 'productID', FILTER_SANITIZE_NUMBER_INT));
		
		// retrieve product
		$stmt = $db->prepare("SELECT * FROM product WHERE ID=:ID");
		$stmt->bindValue(':ID', $productID, SQLITE3_INTEGER);
		$result = $stmt->execute();
		
		if (! sql_is_result_empty($result)) {
			$product = $result->fetchArray();
			
			if ($db->delete_product($productID)) {
				// try removing associated image file
				@unlink($product['imagePath']);
				
				alert('Product was removed successfully');
			} else
				alert('Failed to remove product!');
		}
	}
?>

<head>
  <title>Product Manager | Good Merchandise</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<?php 
	include "Base/header.php";
?>
  <fieldset>
    <legend>Create product</legend>
    <form method="post" enctype="multipart/form-data">
      <div class="field">
	    <label for="name">Name</label>
	    <input type="text" name="name" value="<?php if (isset($_POST['name'])) echo htmlspecialchars($_POST['name']); ?>">
	  </div>
      <div class="field">
	    <label for="categoryID">Category</label>
		<select name="categoryID">
<?php
	$subcats = $db->select_subcategories();
	sql_echo_result_as_options($subcats);
?>
        </select>
      </div>
      <div class="field">
	    <label for="description">Description</label>
	    <textarea name="description" cols="20" rows="10"><?php 
		  if (isset($_POST['description'])) echo htmlspecialchars($_POST['description']); 
		?></textarea>
	  </div>
      <div class="field">
	    <label for="price">Price ($)</label>
	    <input type="text" name="price" value="<?php if (isset($_POST['price'])) echo htmlspecialchars($_POST['price']); ?>">
	  </div>
      <div class="field">
	    <label for="unit">Unit</label>
	    <input type="text" name="unit" value="<?php if (isset($_POST['unit'])) echo htmlspecialchars($_POST['unit']); ?>">
	  </div>
      <div class="field">
	    <label for="image">Image</label>
	    <input type="file" name="image">
	  </div>
	  <br><input type="submit" name="create" value="Create">
    </form>
  </fieldset>
  <fieldset>
    <legend>Delete product</legend>
    <form method="post">
      <div class="field">
	    <label for="productID">Name</label>
		<select name="productID">
<?php
	// loop through each subcategory, adding products under the category to options
	$subcats = $db->select_subcategories();
	while ($subcat = $subcats->fetchArray()) {
		$name = $subcat['name'];
		$ID = $subcat['ID'];
		
		$queryString = "SELECT * FROM product WHERE category_ID=$ID";
		$products = $db->query($queryString);
		
		echo "<optgroup label=\"$name\">";
		sql_echo_result_as_options($products);
		echo '</optgroup>';
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