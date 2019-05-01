<?php
	include_once(__DIR__."/common.php");
	include_once(__DIR__."/sql.php");
	
	/*  Database for products/categories
	
		Supercategory = a category with no parent category.
		Subcategory = a category with a supercategory as parent category.
		
		Note that only 2 levels are supported!!
	*/
	class Database extends SQLite3 {
		function __construct($name) {
			$this->open($name);
		}
		
		/* () -> SQ3LiteResult
		
			Returns query result that contains records of all supercategories.
		*/
		function select_supercategories() { 
			return $this->query("SELECT * FROM category WHERE parent is NULL");
		}
		
		/* (integer $parent = NULL) -> SQ3LiteResult
		
			$parent = ID of supercategory
			
			Returns query result that contains records of all subcategories.
			If $parent is defined, only returns records of subcategories under the supercategory.
		*/
		function select_subcategories($parent = NULL) {
			if ($parent) {
				$stmt = $this->prepare("SELECT * FROM category WHERE parent=:parent");
				$stmt->bindValue(':parent', $parent, SQLITE3_INTEGER);
				$result = $stmt->execute();
			} else
				$result = $this->query("SELECT * FROM category WHERE parent is not NULL");
			
			return $result;
		}
		
		/* (string $name, integer $parent = NULL) -> void
		
			$name = category's name
			$parent = supercategory's ID
			
			Creates a category on database; the category is created under $parent if it is defined.
			Throws exceptions with appropriate error messages when failed.
			
			REQ: if $parent is defined, it must be an ID of a valid supercategory
		*/
		function create_category($name, $parent = NULL) {
			if (empty($parent)) { // create supercategory
				$stmt = $this->prepare("INSERT INTO category (name) VALUES (:name)");
				$stmt->bindValue(':name', $name, SQLITE3_TEXT);
			} else { // create subcategory
				// retrieve parent category info
				$stmt = $this->prepare("SELECT parent FROM category WHERE ID=:ID LIMIT 1");
				$stmt->bindValue(':ID', $parent, SQLITE3_INTEGER);
				$result = $stmt->execute();
				
				if (sql_is_result_empty($result))
					throw new Exception("Parent category does not exist");
				
				$parentCategory = $result->fetchArray();
				if (!empty($parentCategory['parent']))
					throw new Exception("Parent category is not a valid supercategory");
				
				$stmt = $this->prepare("INSERT INTO category (name, parent) VALUES (:name, :parent)");
				$stmt->bindValue(':name', $name, SQLITE3_TEXT);
				$stmt->bindValue(':parent', $parent, SQLITE3_INTEGER);
			}
			
			if (!($stmt->execute() && $this->changes() != 0))
				throw new Exception("Category could not be created");
		}
		
		/* (integer $categoryID) -> bool
		
			Deletes category on database matching given ID.
			All products on the category are deleted as well.
			All of its subcategories are deleted as well.
			
			Returns TRUE if deletion was successful, FALSE if not.
		*/
		function delete_category($categoryID) {
			// retrieve category info
			$stmt = $this->prepare("SELECT * FROM category WHERE ID=:ID LIMIT 1");
			$stmt->bindValue(':ID', $categoryID, SQLITE3_INTEGER);
			$result = $stmt->execute();
			
			if (sql_is_result_empty($result))
				// category not found!
				return false;
			$category = $result->fetchArray();
			
			// delete category
			$stmt = $this->prepare("DELETE FROM category WHERE ID=:ID");
			$stmt->bindValue(':ID', $categoryID, SQLITE3_INTEGER);
			
			if (!($stmt->execute() && $this->changes() != 0))
				// failed to delete category
				return false;
			
			if (empty($category['parent'])) { // if supercategory
				// remove products from subcategories
				$subcats = $this->select_subcategories($categoryID);
				while ($subcat = $subcats->fetchArray()) {
					$subcatID = $subcat['ID'];
					$this->exec("DELETE FROM product WHERE category_ID=$subcatID");
				}
				
				// delete subcategories
				$stmt = $this->prepare("DELETE FROM category WHERE parent=:ID");
				$stmt->bindValue(':ID', $categoryID, SQLITE3_INTEGER);
				$stmt->execute();
			} else { // if subcategory
				// remove products
				$stmt = $this->prepare("DELETE FROM product WHERE category_ID=:categoryID");
				$stmt->bindValue(':categoryID', $categoryID, SQLITE3_INTEGER);
				$stmt->execute();
			}
			
			return true;
		}
		
		/* (integer $productID) -> array
		
			Return array containing product record whose ID matches $productID.
			Returns NULL if there isn't a matching record.
		*/
		function select_product($productID) { 
			$stmt = $this->prepare("SELECT * FROM product WHERE ID=:ID");
			$stmt->bindValue(':ID', $productID, SQLITE3_INTEGER);
			$result = $stmt->execute();
			
			if (sql_is_result_empty($result))
				return NULL;
			
			return $result->fetchArray();
		}
		
		/* (string $name, integer $categoryID, string $description, float $price, string $unit, string $imagePath) -> void
		
			$name = product's name
			$categoryID = product's category ID
			$description = product's description
			$price = product's price
			$unit = product's measurement of unit
			$imagePath = product's image path on root
			
			Creates a product on database with given information.
			Throws exceptions with appropriate error messages when failed.
			
			REQ: name/categoryID/price must not be empty; categoryID must reference a valid subcategory in database
		*/
		function create_product($name, $categoryID, $description, $price, $unit, $imagePath) {
			if (empty($name))
				throw new Exception("Product must have a name");
			
			if (empty($categoryID))
				throw new Exception("Product must have a category");
			
			$stmt = $this->prepare("SELECT ID FROM category WHERE ID=:ID AND parent is not NULL");
			$stmt->bindValue(':ID', $categoryID, SQLITE3_INTEGER);
			
			if (sql_is_result_empty($stmt->execute()))
				throw new Exception("Category must be a valid subcategory");
			
			if (empty($price))
				throw new Exception("Product must have a price");
			
			// empty strings are treated as NULL
			if (empty($description)) $description = NULL;
			if (empty($unit)) $unit = NULL;
			if (empty($imagePath)) $imagePath = NULL;
			
			$queryString = "INSERT INTO product (name, category_ID, description, price, unit, imagePath) 
								VALUES (:name, :categoryID, :description, :price, :unit, :imagePath)";
			$stmt = $this->prepare($queryString);
			$stmt->bindValue(':name', $name, SQLITE3_TEXT);
			$stmt->bindValue(':categoryID', $categoryID, SQLITE3_INTEGER);
			$stmt->bindValue(':description', $description, SQLITE3_TEXT);
			$stmt->bindValue(':price', $price, SQLITE3_FLOAT);
			$stmt->bindValue(':unit', $unit, SQLITE3_TEXT);
			$stmt->bindValue(':imagePath', $imagePath, SQLITE3_TEXT);
			
			if (!($stmt->execute() && $this->changes()))
				// if query or insertion was unsuccessful successful
				throw new Exception("Product could not be created");
		}
		
		/* (integer $productID) -> bool
		
			Deletes product on database matching given ID.
			Returns TRUE if deletion was successful, FALSE if not.
		*/
		function delete_product($productID) {
			$stmt = $this->prepare("DELETE FROM product WHERE ID=:ID");
			$stmt->bindValue(':ID', $productID, SQLITE3_INTEGER);
			
			return ($stmt->execute() && $this->changes());
		}
	}
	
	$db = new Database(__DIR__."/../productInfo.db");
?>