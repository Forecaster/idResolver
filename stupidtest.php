<?php
include("inc_functions.php");

$string = "/LostBooks/folder.cfg";

$explode = explode('/', $string);

$explode = cleanArray($explode);

myVarDump($explode);
?>