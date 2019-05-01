<?php
	include_once("lib/common.php");
?>
<!DOCTYPE html>
<html>
<?php
	if (isset($_POST['restoreSite'])) {
		copy('Backup/productInfo.db', 'productInfo.db');
		recurse_copy('Backup/Image/Product', 'Image/Product');
		
		alert("Site restored.");
	}
?>

<head>
  <title>About | Good Merchandise</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
  <?php include "Base/header.php"; ?>
  <div id="contents">
    <p>
	  All images used on this site were downloaded from Google Image Search.<br>
	  Only to be used for personal use.
	</p>
	<a class="button" href="manage_category.php">Manage Category</a>
	<a class="button" href="manage_product.php">Manage Products</a>
	<form id="restoreSite" method="post" class="inline-block">
	  <input type="hidden" name="restoreSite">
	  <a class="button" onclick="document.getElementById('restoreSite').submit();">Restore Site</a>
	</form>
  </div>
  <?php include "Base/footer.php"; ?>
</body>

</html>