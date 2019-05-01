<?php 
	include_once("lib/common.php");
	include_once("lib/sql.php");
	include_once("lib/database.php");
	
	session_start();
?>
<!DOCTYPE html>
<?php
	if (isset($_POST['remove'])) {
		$productID = $_POST['remove'];
		
		if (isset($_SESSION['purchaseCount'][$productID]))
			unset($_SESSION['purchaseCount'][$productID]);
	} elseif (isset($_POST['clear'])) {
		if (isset($_SESSION['purchaseCount']))
			unset($_SESSION['purchaseCount']);
	}
?>
<html>

<head>
  <title>Cart | Good Merchandise</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
<?php
	include "Base/header.php";
?>
<?php	
	if (!empty($_SESSION['purchaseCount'])) {
		$priceTotal = 0;
?>
  <table id="cart-table">
    <thead>
	  <tr>
	    <td>Product Name</td>
        <td>Quantity</td>
		<td>Price</td>
	  </tr>
	</thead>
	<tbody>
<?php
		foreach ($_SESSION['purchaseCount'] as $productID => $count) {
			$product = $db->select_product($productID);
			
			if (empty($product))
				// product not found; remove from cart
				unset($_SESSION['purchaseCount'][$productID]);
			else {
				$productName = $product['name'];
				$productUnit = $product['unit'];
				$productPriceTotal = $count * $product['price'];
				$priceTotal += $productPriceTotal;
?>
	  <tr>
	    <td><?php echo $productName; ?></td>
	    <td><?php echo $count.(empty($productUnit) ? '' : ' '.$productUnit.'(s)'); ?></td>
	    <td><?php echo '$'.$productPriceTotal; ?></td>
		
		<!-- cancel button; this element is omitted from the table flow -->
	    <td class="cancel" onclick="document.getElementById('<?php echo "removeItem$productID"; ?>').submit();">
		  <form id="<?php echo "removeItem$productID"; ?>" method="post">
		    <input type="hidden" name="remove" value="<?php echo $productID; ?>">
			<i class="fa fa-remove"></i>
		  </form>
		</td>
	  </tr>
<?php
			}
		}
?>
    </tbody>
    <tfoot>
	  <tr>
	    <td>Total</td>
	    <td></td>
	    <td><?php echo '$'.$priceTotal; ?></td>
	  </tr>
	</tfoot>
  </table>
  <form method="post">
    <input type="submit" name="proceed" value="Checkout (not active)">
    <input type="submit" name="clear" value="Clear">
  </form>
<?php
	} else {
?>
  <p>
    Your cart is empty!
  </p>
<?php
	}
	include "Base/footer.php";
?>
</body>

</html>