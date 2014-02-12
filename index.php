<?php
$securitytoken = "alphaspacepie";

require_once "inc_functions.php";
require_once "inc_connect.php";
$fileSizeLimit = 2097152;

{ ### ERRORS
$str_error[1] = "Invalid file type. Make sure your archive is in the .zip format.";
$str_error[2] = "File is too large.";
$str_error[3] = "File error.";
$str_error[4] = "File with same name already exists.";
$str_error[5] = "Failed to move file.";
}

@$step = $_POST['step'];

$search[] = ".cfg";
$search[] = ".conf";
$search[] = ".txt";

/*{ ### READ COMPAT FILES
$compatEntries = myReadDir('compat/', $search, null, null, 0, ($debug -4));

foreach ($compatEntries as $compatEntriesKey => $compatEntriesValue)
{
  if (($debug -4) > 2) echo "[Debug][CompatArray]Adding " . $compatEntriesValue['path'] . "<br>";
  $compat[$compatEntriesValue['path']]['path'] = $compatEntriesValue['path'];
}

foreach ($compat as $compatKey => $compatValue)
{
  $path = "compat/" . $compatValue['path'];
  $compat[$compatKey]['content'] = myReadFile($path);
}

foreach ($compat as $compatKey => $compatValue)
{
  unset($return);
  if (($debug -4) > 0) echo "<div class=warning>[Debug][compat]Reading " . $compatValue['path'] . "</div>";
  $return = readCompat($compatValue['content'],  ($debug -4));
  
  if (($debug -4) > 0) echo "<div class=warning>[Debug][CompatReturn] $return</div>";
  
  if (is_array($return))
  {
    list($shifted, $blockblocks, $itemblocks, $blocks, $items, $blockranges, $itemranges) = $return;
    $compat[$compatKey]['ids'] = 'yes';
    $compat[$compatKey]['ignore'] = 'no';
    $compat[$compatKey]['preshifted'] = $shifted;
    $compat[$compatKey]['blockblocks'] = $blockblocks;
    $compat[$compatKey]['itemblocks'] = $itemblocks;
    $compat[$compatKey]['blocks'] = $blocks;
    $compat[$compatKey]['items'] = $items;
    $compat[$compatKey]['blockranges'] = $blockranges;
    $compat[$compatKey]['itemranges'] = $itemranges;
  }
  elseif (is_string($return))
  {
    $noids = 0;
    $ignore = 0;
    $unsupp = 0;
    if ($return == "noids")
      $noids = 1;
    elseif ($return == "ignore")
      $ignore = 1;
   elseif ($return == "unsupported")
      $unsupp = 1;
    
  }
}
}
*/

#myVarDump($compat);

$defaultBlockCategories = array('block {', 'blocks {', 'block_ids {', 'blocks_ids {');
$defaultItemCategories = array('item {', 'items {', 'item_ids {', 'items_ids {');

{ ### RESERVED ID'S
$reservedVanillaBlocks = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173);

$reservedVanillaItems = array(256,257,258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,419,420,421,2256,2257,2258,2259,2260,2261,2262,2263,2264,2265,2266,2267);
}

$startblock = 600;
$startitem = 4096;

$spaceblock = 25;
$spaceitem = 256;

$maxBlock = 4095;
$maxItem = 31999;

$shiftValue = 256;

$indent = 0;
?>
<script language="javascript" src="scripts.js"></script>

<HTML>
<link rel="stylesheet" type="text/css" href="styles.css"></link>

<body>

<div style='z-index: 10; position: fixed; left: 0px; top: 0px; background-color: red; color: black; width: 100%; font-size: 24pt; font-weight: bold;'>This is a beta that is being actively worked on! It may randomly break or not work correctly! It should not be used other than for testing! -Forecaster</div>
<div style='height: 80px;'></div>
<div class=title>
  <div class=inline><img id=logo src='logo50x50.png' onMouseOver='document.getElementById("title").innerHTML="Spacepie!"; document.getElementById("logo").src="spacepie.png";' onMouseOut='document.getElementById("title").innerHTML="The Minecraft ID Resolver"; document.getElementById("logo").src="logo50x50.png";'></img></div>
  <div id=title style='cursor: default;' class=inline>The Minecraft ID Resolver</div>
</div>
<div class=subTitle>by Forecaster</div>
<div id=noscript class=noscript>
    <div>Hi! It appears you have javascript disabled! This web-application is designed with this in mind, but be aware that some features are going to be unavaliable to you!</div>
    <div class=topmrgn>The site is going to be messier and you're going to have to do a lot of scrolling because elements can't be hidden and shown dynamically without javascript.</div>
    <div class=topmrgn>Certain things like the "clear" buttons are going to do nothing for example.</div>
</div>
<script>hide(document.getElementById("noscript"));</script>
<div class=divider></div>

<?php
  session_start();
  
  if (isset($_POST['debug']))
    $debug = $_POST['debug'];
  elseif (isset($_SESSION['debug']))
    $debug = $_SESSION['debug'];
  else
    $debug = 0;
  
  if (isset($debug) && $debug > 0)
  {
    echo "<div class=debug>Session ID: " . session_id() . "</div>";
    echo "<div class=debug>Debug $debug</div>";
  }
    
  if (isset($_SESSION['config']))
    $config = $_SESSION['config'];
  
  if (isset($_POST['key']))
    $filekey = $_POST['key'];
  else
    $filekey = null;
  
  if (isset($_SESSION['temp_compat']))
    $temp_compat = $_SESSION['temp_compat'];
  else
    $temp_compat = null;

$step = $_POST['step'];
if (!isset($step) || $step == 'mode')
  include("inc_step_upload.php");
  #include("inc_step_mode.php");

#if ($step == 'upload')
  #include("inc_step_upload.php");

if ($step == 'compat')
  include("inc_step_compat.php");

if ($step == 'analysis')
  include("inc_step_analysis.php");

if ($step == 'assigning')
  include("inc_step_assigning.php");

if ($step == 'download')
  include("inc_step_download.php");
  
  $_SESSION['config'] = $config;
  $_SESSION['debug'] = $debug;
  $_SESSION['temp_compat'] = $temp_compat;
  
  if ($filekey != "demo")
    $_SESSION['filekey'] = $filekey;
  else
    unset($_SESSION['filekey']);
?>
</HTML>

<?php require_once("inc_stat.php");?>
