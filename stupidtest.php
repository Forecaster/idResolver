<link rel="stylesheet" type="text/css" href="styles.css"></link>
<?php
include("inc_functions.php");

$string = "spacepie";

$target = "pie";

$result = str_in_str($target, $string);
?>

<HTML>
<div class=pnt onClick='this.style.color="red"; this.style.opacity="0.2";'><?php if ($result) echo "true"; else echo "false";?></div>