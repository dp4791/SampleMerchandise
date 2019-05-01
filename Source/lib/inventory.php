<?php
	include_once(__DIR__."/common.php");
	
	class Item {
		private $ID;
		private $name;
		private $imagePath;
		private $price;
		private $description;
		private $unit;
		
		function __construct($ID, $name, $price, $unit, $description, $imagePath) {
			$this->ID = $ID;
			$this->name = $name;
			$this->price = $price;
			$this->unit = $unit;
			$this->description = $description;
			$this->imagePath = $imagePath;
		}
		static function createFromArray($array) {
			$ID = $array['ID'];
			$name = $array['name'];
			$price = $array['price'];
			$unit = $array['unit'];
			$description = $array['description'];
			$imagePath = $array['imagePath'];
			
			return new Item($ID, $name, $price, $unit, $description, $imagePath);
		}
		
		function getID() { return $this->ID; }
		function getName() { return $this->name; }
		function getFormattedName() { return "<b>".$this->name."</b>"; }
		
		function getPrice() { return $this->price; }
		function getUnit() { return $this->unit; }
		function getFormattedPrice() { 
			$priceString = "$<i>".$this->price."</i>";
			
			if ($this->unit !== NULL)
				$priceString .= " / $this->unit";
				
			return $priceString;
		}
		
		function getDescription() { return $this->description; }
		// linebreak ('\n') in MySQL text to linebreak(<br>) in HTML
		function getFormattedDescription() { return nl2br($this->description); }
		
		function getImagePath() { return $this->imagePath; }
	}
	
	class Inventory {
		private $items;
		
		function __construct() {
			$this->items = array();
		}
		
		function getItems() {
			return $this->items;
		}
		function addItem($item) {
			array_push($this->items, $item);
		}
		
		function __destruct() {
			foreach ($this->items as $item) {
				unset($item);
			}
		}
	}

?>