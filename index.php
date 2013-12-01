<?php
require_once "inc_functions.php";

{ ### ERRORS
$str_error[1] = "Invalid file type.";
$str_error[2] = "File is too large.";
$str_error[3] = "File error.";
$str_error[4] = "File with same name already exists.";
$str_error[5] = "Failed to move file.";
}

{ ### GET FORM DATA
@$step = $_POST['step'];

if (isset($_POST['debug']))
  @$debug = $_POST['debug'];
else
  @$debug = 0;
}

{ ### CONFIG IGNORE LIST  $ignore[] = "file";

$ignore[] = "forge.cfg";
$ignore[] = "forgeChunkLoading.cfg";
$ignore[] = "multipart.cfg";
$ignore[] = "microblocks.cfg";
$ignore[] = "modstats.cfg";
$ignore[] = "GenerationManager.cfg";
$ignore[] = "NEI.cfg";
$ignore[] = "NEIServer.cfg";
$ignore[] = "NEISubset.cfg";
$ignore[] = "WirelessRedstone.cfg";
$ignore[] = "denLib.cfg";
$ignore[] = "NEI-Mystcraft-Plugin.cfg";
$ignore[] = "Waila.cfg";
$ignore[] = "powersuits-keybinds.cfg";
$ignore[] = "WebDisplay.cfg";
$ignore[] = "BetterGrassAndLeavesMod.cfg";
$ignore[] = "colossali_Super_Heroes.cfg";
$ignore[] = "mod_StatusEffectHUD.cfg";
$ignore[] = "mod_ArmorStatusHUD.bsprop.cfg";
$ignore[] = "NEIAddons.cfg";
}

{ ### PRE SHIFTED CONFIGS
$ignoreShift[] = "factorization.cfg";
}

$search[] = ".cfg";
$search[] = ".conf";
$search[] = ".txt";

#$blockBlocks = array('block {', 'blocks {', 'blocks_ids {', '"lordmau5/powerboxes/block" {');

#$itemBlocks = array('item {', 'items {', 'equipables {', 'logic {', '"patterns and misc" {', '"tool parts" {', 'tools {', 'items_ids {', 'signedittool {', 'item.ids {', '"lordmau5/powerboxes/item" {');

{ ### READ COMPAT FILES
$compatEntries = myReadDir('compat/', $search, null, null, $debug);

foreach ($compatEntries as $compatEntriesKey => $compatEntriesValue)
{
  if ($debug > 2) echo "[Debug][CompatArray]Adding " . $compatEntriesValue['path'] . "<br>";
  $compat[$compatEntriesValue['path']]['path'] = $compatEntriesValue['path'];
}

foreach ($compat as $compatKey => $compatValue)
{
  $path = "compat/" . $compatValue['path'];
  $compat[$compatKey]['content'] = myReadFile($path);
}

foreach ($compat as $compatKey => $compatValue)
{
  #echo "<div class=warning>[Debug][compat]Reading " . $compatValue['path'] . "</div>";
  list($shifted, $ids, $blockblocks,$itemblocks, $blocks, $items, $blockranges, $itemranges) = readCompat($compatValue['content'],  $debug);
  $compat[$compatKey]['ids'] = $ids;
  $compat[$compatKey]['shifted'] = $shifted;
  $compat[$compatKey]['blockblocks'] = $blockblocks;
  $compat[$compatKey]['itemblocks'] = $itemblocks;
  $compat[$compatKey]['blocks'] = $blocks;
  $compat[$compatKey]['items'] = $items;
  $compat[$compatKey]['blockranges'] = $blockranges;
  $compat[$compatKey]['itemranges'] = $itemranges;
}
}

#myVarDump($compat);

$defaultBlockblocks = array('block {', 'blocks {');
$defaultItemblocks = array('item {', 'items {');

{ ### RESERVED ID'S
$reservedVanillaBlocks = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173);

$reservedVanillaItems = array(256,257,258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,419,420,421,2256,2257,2258,2259,2260,2261,2262,2263,2264,2265,2266,2267);
}

$startblock = 600;
$startitem = 4096;

$maxBlock = 4095;
$maxItem = 31999;

$indent = 0;
?>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js" /></script>

<script type="text/javascript">
$(document).ready(function(){
$('input[name="all"],input[name="title"]').bind('click', function(){
var status = $(this).is(':checked');
$('input[type="checkbox"]', $(this).parent('div')).attr('checked', status);
});
});
</script>

<HTML>
<link rel="stylesheet" type="text/css" href="styles.css" />

<head>
<title>Minecraft ID Resolver</title>
</head>

<body>

<?php

{
$step = $_POST['step'];
if (!isset($step) || $step == 'mode')
{
  if (!isset($step))
    $step = 'mode';

  step($step);
  
  echo "
  <div>
    <form action='' method=post>
      <input type=hidden name=step value='upload' />
      <input class=button2 type=submit value='ID Mode' />
    </form>
  </div>
  <div>
    <form action='' method=post>
      <input class=button2 type=submit value='Biome Mode' disabled style='background-color: gray;'/>
    </form>
  </div>
  <div>
    <form action='' method=post>
      <input class=button2 type=submit value='Settings Mode' disabled style='background-color: gray;'/>
    </form>
  </div>";
}
}

{
$step = $_POST['step'];
if ($step == 'upload')
{
  
  step($step);
  echo "
  <div style='font-size: 24pt; font-weight: bold;'>Archive all your files in the config folder as a .zip archive.</div>
  <div style='font-size: 18pt; font-weight: bold;'>Upload below:</div>
  <br>
  <form action='' method='post' enctype='multipart/form-data'>
    <input type=hidden name=step value='overview' />
    <input type=file name=file style='width: 100%; height: 50px; background-color: lightgray;' /><br>
    <input class=button type=submit value='Upload' />
    <input type=text name=debug placeholder='Debug Level' /> Debug level. (0 - 4) Determines amount of debug output where 0 is none. There will still be normal output though.<br>
  </form>
  <br>
  Or<br>
  <br>
  Enter key to access previous file (Not implemented yet):<br>
  <form action='' method='post'>
    <input type=hidden name=step value=4 />
    <input type=text name=key placeholder='Key' style='width: 100%;'/>
    <input class=button type=submit value='Submit Key' />
  </form>
  <br>
  <span style='font-weight: bold;'>Note that certain mods do not use the forge config format!</span><br>
  <br>
  Although not finding any id's in a file will not break anything, the resolver will not be able to change the id's in the file, or take them into account when assigning id's for other files.<br>
  <br>
  In forge configs block and item id's are kept within block{} and item{} blocks, these are what the resolver is looking for when extracting id's.<br>
  <br>
  Certain mods have other names for the blocks, which means the resolver will not be able to tell that there are id's within without being told so.<br>
  <br>
  For example chickenbones wireless redstone mod, it uses several custom blocks with different names that do not indicate whether they contain id's or not.<br>
  <br>
  There are also other options mixed in with the id's in these blocks that complicate things further.<br>
  <br>
  Thus the wirelessredstone.cfg file is ignored.<br>
  <br>
  Also ignored are files with any file name extension other than .cfg or .conf<br>
  <br>
  What it also will not do is take into account id's that need to be within a specific range, or have surrounding id's clear. You will have to account for this manually.<br>
  <br>
  <span style='font-weight: bold;'>Ignored files:</span><br>
  ";
  
  foreach ($ignore as $value)
    echo $value . "<br>";
}
}

{
$step = $_POST['step'];
if ($step == 'overview')
{
  step($step);
  list($error, $filekey) = recieveFile('file');
  
  session_start();
  
  if ($error == 0)
  {
    echo "
    Your key: <div id=key class=key>" . $filekey . "</div><br>
    Copy this key. Should you be unable to download the archive in the final step this can be used to recover it. You should also include this when reporting bugs.<br>
    <br>
    Now you may overview the id's that were found in the configs you provided.<br>
    You may tick the box before any id to lock it. This will exclude this option from being assigned a new id in the next step as well as exclude the same id from the assigning process.<br>
    <br>
    Here you may specify the starting values at which block and item id's will start being assigned.<br>
    These will override the default values. Leave blank to use the defaults.<br>
    <form action='' method=post>
   <input type=text size=40 name=startblock placeholder='Starting block ID (Default: $startblock)' />
   <input type=text size=40 name=startitem placeholder='Starting item ID (Default: $startitem)' /><br>
   <input class=button type='submit' value='Next' />";
    
    $archivepath = "archives/$filekey.zip";
    $targetpath = "extracted/$filekey";
    
    #list($times_read, $configs, $configValues, $names) = readZip($path . $filename, $ignore, 2);
    
    if (!extractZip($archivepath, $targetpath))
      echo "<div class=error>[Error]Something went wrong when trying to extract your archive!</div>";
    
    $dirpath = "extracted/$filekey";
    
    $entries = myReadDir($dirpath, $search, null, null, $debug);
    
    asort($entries);
    
    foreach ($entries as $entriesKey => $entriesValue)
    {
      $config[$entriesKey]['path'] = $entriesValue['path'];
      $config[$entriesKey]['name'] = $entriesValue['name'];
    }
    
    foreach ($config as $configKey => $configValue)
    {
      $skip = 0;
      $filepath = "extracted/$filekey/" . $configValue['path'];
      
      $contents = myReadFile($filepath);
      if (!$contents)
      {
        echo "<div class=note>[Note]File " . $configValue['path'] . " is empty! Skipping!</div>";
        unset($config[$configKey]);
        $skip = 1;
      }
      else
        $config[$configKey]['contents'] = $contents;
      
      if ($skip == 0)
      {
        $config[$configKey]['newContents'] = $config[$configKey]['contents'];
        
        if ($debug > 0) echo "[Debug][index]Reading file " . $configValue['name'] . ":<br>";
        
        if (!(strtolower($compat[$configValue['name']]['shifted']) == 'yes'))
        {
          if ($debug > 0) echo "[Debug][index]Shifted " . $configValue['name'] . "<br>";
          $shift = 256;
          $config[$configKey]['shifted'] = 256;
        }
        else
        {
          if ($debug > 0) echo "[Debug][index]Ignored shift on " . $configValue['name'] . "<br>";
          $shift = 0;
          $config[$configKey]['shifted'] = 0;
        }
        
        list($config[$configKey]['values'], $config[$configKey]['idCounter']) = extractValues($config[$configKey]['path'], $config[$configKey]['contents'], $compat, $shift, $debug);
        
        if ($config[$configKey]['idCounter'] == 0)
          echo "<div class=warning>[Warning]No id's could be found in " . $configValue['path'] . ". Either there are none, or it contains config blocks with non-standard names! Please report to Forecaster!</div>";
      }
    }
    
    #myVarDump($config);
    
    /*foreach ($config as $key => $value)
    {
      echo "File: " . $value['path'] . "<br>";
      foreach ($value['values'] as $key2 => $value2)
      {
        echo $key2 . " => " . $value2['id'] . "<br>";
      }
    }*/
    
    $_SESSION['debug'] = $debug;
    $_SESSION['config'] = $config;
    $_SESSION['filekey'] = $filekey;
    
    $_SESSION['startblock'] = $startblock;
    $_SESSION['startitem'] = $startitem;
    
    $title_counter = 1;
    $value_counter = 1;
    
    echo "<div id=configs style='border: 1px solid black;'><input type=checkbox name=all id=all /><label for=all> All </label>
    <input type=hidden name=step value='assigning' />
    <input type=hidden name=key value='$filekey' />";
    foreach ($config as $key => $value)
    {
      $name = $value['name'];
      $path = $value['path'];
      $values = $value['values'];
      
      echo "
      <div id=" . $name . " style='border: 1px dashed black;'>
      <div><input type=checkbox name='title_$title_counter' id='title_$title_counter' /><label for='title_$title_counter'>" . $path . "</div>";
      foreach ($values as $key2 => $value2)
      {
        $id = trim($value2['id']);
        $type = $value2['type'];
        $idvalue = $value2['value'];
        
        echo "
        <div class=option>
          <input type=checkbox name='$id' value='$idvalue' id='box_$value_counter' /><label for='box_$value_counter'>$type - $id=$idvalue</label>
        </div>";
        $value_counter++;
      }
      echo "</div>";
      $title_counter++;
    }
    echo "</form></div>";
  }
  else
  {
    echo "Error " . $error . ": " . $str_error[$error];
  }
}
}

{
$step = $_POST['step'];
if ($step == 'assigning')
{
  session_start();
  #session_id(1);

  $debug = $_SESSION['debug'];
  $config = $_SESSION['config'];
  
  if ($_POST['startblock'] > 0)
    $startblock = $_POST['startblock'];
  elseif (isset($_SESSION['startblock']))
    $startblock = $_SESSION['startblock'];
  
  if ($_POST['startitem'] > 0)
    $startitem = $_POST['startitem'];
  elseif (isset($_SESSION['startitem']))
    $startitem = $_SESSION['startitem'];
  
  step($step);
  echo "<div id=key class=key>Key: " . $_SESSION['filekey'] . "</div><br><br>";
  
  echo "
  <div>
    You may review the changes made below, then proceed to the final step where your download will be avaliable.
  </div>
  <div>
    <form action='' method=post>
      <input type=hidden name=step value='download' />
      <input class=button type=submit value=Next />
    </form>
  </div>";
  
  $post_counter = 0;
  foreach ($_POST as $key => $value)
  {
    if (stristr($key, 'I:'))
    {
      #I:"BlockStartingID"=3000
      $locked[] = $key . "=" . $value;
      $post_counter++;
    }
  }
  
  #echo "[Debug]Found $post_counter locked items.<br>";
  
  $newblockidcounter = $startblock;
  $newitemidcounter = $startitem;
  
  ### BLOCK ID ASSIGNING ###
  echo "<br>=====Starting block assign!<br>";
  foreach ($config as $configKey => $configValue)
  {
    foreach ($configValue['values'] as $configValueValue)
    {
      if ($configValueValue['type'] == "block")
      {
        if ($debug > 0) echo "[Debug][blockAssign]Checking for \"" . ($configValueValue['id'] . "=" . $configValueValue['value']) . "\" in locked array!<br>";
        if (!str_in_array(($configValueValue['id'] . "=" . $configValueValue['value']), $locked))
        {
          $assigned = false;
          while ($assigned === false)
          {
            if ($debug > 0) echo "[Debug][blockAssign]Checking if $newblockidcounter is conflicting with vanilla!<br>";
            if (!in_array($newblockidcounter, $reservedVanillaBlocks))
            {
              $target = $configValueValue['id'] . "=" . $configValueValue['value'];
              $needle = $configValueValue['id'] . "=" . $newblockidcounter;
              $config[$configKey]['newContents'] = str_replace($target, $needle, $config[$configKey]['newContents']);
              echo "Changed <div class=target>$target</div> to <div class=needle>$needle</div> <br>";
              $newblockidcounter++;
              $assigned = true;
            }
            else
            {
              if ($debug > 0) echo "[Debug][blockAssign]Ignored id conflicting with vanilla.<br>";
              $newblockidcounter++;
            }
          }
        }
        else
          if ($debug > 0) echo "[Debug][blockAssign]Ignored locked option.<br>";
      }
      #else
        #echo "Ignored non-block.<br>";
    }
  }
  
  ### ITEM ID ASSIGNING ###
  echo "<br>=====Starting item assign!<br>";
  foreach ($config as $configKey => $configValue)
  {
    echo "<div class=warning>File: " . $configValue['name'] . "</div>";
    
    foreach ($compat[$configValue['name']]['itemranges'] as $compatKey => $compatValue)
    {
      $localCompat[$compatValue['key']] = $compatValue['range'];
    }
    
    foreach ($configValue['values'] as $configValueValue)
    {
      if ($configValueValue['type'] == "item")
      {
        if ($debug > 0) echo "[Debug][itemAssign]Checking for \"" . ($configValueValue['id'] . "=" . $configValueValue['value']) . "\" in locked array!<br>";
        if (!in_array($configValueValue['id'] . "=" . $configValueValue['value'], $locked))
        {
          $assigned = false;
          while ($assigned === false)
          {
            if ($debug > 0) echo "[Debug][itemAssign]Checking if $newitemidcounter is conflicting with vanilla!<br>";
            if (!in_array($newitemidcounter, $reservedVanillaItems))
            {
              if (in_array($configValue['name'], $ignoreShift))
                $target = $configValueValue['id'] . "=" . ($configValueValue['value'] - $configValue['shifted']);
              else
                $target = $configValueValue['id'] . "=" . $configValueValue['value'];
              
              if ($debug > 0) echo "[Debug][itemAssign]Shift value: " . $configValue['shifted'] . "<br>";
              
              if (in_array($configValue['name'], $ignoreShift))
                $needle = $configValueValue['id'] . "=" . ($newitemidcounter - $configValue['shifted']);
              else
                $needle = $configValueValue['id'] . "=" . $newitemidcounter;
              
              $config[$configKey]['newContents'] = str_replace($target, $needle, $config[$configKey]['newContents']);
              echo "[Debug][itemAssign]Changed <div class=target>$target</div> to <div class=needle>$needle</div> <br>";
              
              $currentKey = strip($configValueValue['id']);
              
              $assigned = true;
              if ($configValue['name'] == "PortalGun.cfg")
              {
                echo "<div>Looking for '" . $currentKey . "' in localCompat</div>";
                echo "<div>Key: " . key_in_array($currentKey, $localCompat) . "</div>";
              }
              
              #myVarDump($localCompat);
              
              
              if (key_in_array($currentKey, $localCompat))
              {
                echo "<div>Increased item id counter by " . ($localCompat[$currentKey] - 1) . "</div>";
                $newitemidcounter + ($localCompat[$currentKey] - 1);
              }
              else
                $newitemidcounter++;
            }
            else
            {
              if ($debug > 0) echo "[Debug][itemAssign]Ignored id conflicting with vanilla.<br>";
              $newitemidcounter++;
            }
          }
        }
        else
          echo "Ignored locked option.<br>";
      }
      #else
        #echo "Ignored non-item.<br>";
    }
  }
  
  #myVarDump($config);

  $_SESSION['config'] = $config;
  
  /*echo "New configs:<br>";
  foreach ($config as $key => $value)
  {
    echo $key . " => " . $value['name'] . "<br>" . nl2br($value['newContents']) . "<br>";
  }*/
}
}

{
$step = $_POST['step'];
if ($step == 'download')
{
  session_start();
  $debug = $_SESSION['debug'];
  $config = $_SESSION['config'];
  $filekey = $_SESSION['filekey'];
  
  step($step);
  echo "<div id=key class=key>" . $filekey . "</div><br>";
  
  $debug = 0;
  foreach ($config as $key => $value)
  {
    $filepath = "extracted/$filekey/" . $value['path'];
    if ($debug > 0) echo "[Debug]Working in\"$filepath\" on " . $value['path'] . "<br>";
    
    if (writeToFile($value['newContents'], $filepath))
      if ($debug >= 1) echo "Success on " . $value['path'] . "!<br>";
    else
      if ($debug >= 1) echo "Fail on " . $value['path'] . "!<br>";
  }
  
  /*
  foreach ($config as $key => $value)
  {
    $addpath = "extracted/$filekey/" . $value['path'];
    $targetpath = "repacked/$filekey.zip";
    
    fopen($targetpath, 'w');
    
    $newname = $value['path'];
    
    echo "Adding " . $value['name'] . " from <r>$addpath</r> to <r>$targetpath</r> as <r>$newname</r><br>";
    
    if (addFile($config, $targetpath, $newname))
      echo "Success on " . $value['path'] . "!<br>";
    else
      echo "Fail on " . $value['path'] . "!<br>";
  }*/
  
  $targetpath = "repacked/$filekey.zip";
  $result = addFiles($filekey, $config, $targetpath, $debug);
  
  rrmdir("extracted/$filekey");
  unlink("archives/$filekey" . ".zip");
  
  $dirpath = "repacked/";
  
  $searchfor[] = ".zip";
  $ignore[] = "index.php";
  
  $archives = myReadDir($dirpath, $searchfor, $ignore, null, $debug);
  
  foreach ($archives as $value)
  {
    $name = "repacked/" . $value['name'];
    $datetime = filemtime($name);
    if ($datetime !== false)
      if (($datetime + 86400) < time())
      {
        unlink($name);
        if ($debug > 0) echo "[Debug]Deleted $name.";
      }
  }
  
  if (!$result)
    echo "Error while attempting to archive! Please retry!<br>";
  else
    echo "Archiving succeeded! You will find your file here: <a href='$targetpath'>[DOWNLOAD]</a><br>
    <br>
    Download the file into your config directory (You should make a backup of it first) then right click it and \"Extract here\" (assuming you are using WinRAR) overwrite everything.<br>
    <br>
    Should you need to redownload the file later it will remain for 24h. Use your key in step one to gain access to it, or give access to someone else.";
  
  session_write_close();
}
}

?>
</HTML>
