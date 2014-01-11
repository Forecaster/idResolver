<?php
require_once "inc_functions.php";
$fileSizeLimit = 2097152;

{ ### ERRORS
$str_error[1] = "Invalid file type. Make sure your archive is in the .zip format.";
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

$search[] = ".cfg";
$search[] = ".conf";
$search[] = ".txt";

{ ### READ COMPAT FILES
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
    if ($return == "noids")
      $compat[$compatKey]['ids'] = 'no';
    elseif ($return == "ignore")
      $compat[$compatKey]['ignore'] = 'yes';
   elseif ($return == "unsupported")
      $compat[$compatKey]['unsupported'] = 'yes';
  }
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

$shiftValue = 256;

$indent = 0;
?>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
<script language="javascript" src="scripts.js"></script>

<HTML>
<link rel="stylesheet" type="text/css" href="styles.css"></link>

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
  <head>
    <title>Minecraft ID Resolver - Mode Step</title>
  </head>";
  
  echo "
  <div class=center>
    <form action='#fromMode' method=post>
      <input type=hidden name=step value='upload' />
      <input class=button2 type=submit value='ID Resolver Mode' />
    </form>
  </div>
  <div class=divider></div>
  <div class=center>
    <form action='' method=post>
      <input class=button2 type=submit value='Biome Resolver Mode (N/A)' disabled style='background-color: gray;'/>
    </form>
  </div>
  <div class=divider></div>
  <div class=center>
    <form action='' method=post>
      <input class=button2 type=submit value='Settings Mode (N/A)' disabled style='background-color: gray;'/>
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
  <head>
    <title>Minecraft ID Resolver - Upload Step</title>
  </head>";
  
  echo "
  <div style='font-size: 24pt; font-weight: bold;'>Archive all your files in the config folder as a <r>[.zip]</r> archive.</div>
  <div style='font-size: 14pt;'>Other archive types will <r>NOT</r> work.</div>
  <div class=divider></div>
  <div>What this does, in brief:</div>
  <div class=divider></div>
  <div>The Analysis step (the first after uploading) will scan the uploaded files, extract all the id's it can, tell you how many it found, which files didn't have any, which files where empty etc, it then sifts through all the found id's and will detect conflicts. You may stop here if you only wished to know if there are any conflicts in your configs. If you wish to proceed you may check the boxes by an item to lock it, preventing the next step from interracting with it.</div>
  <div class=divider></div>
  <div>The Assigning step will, as the name implies, assign new id's to any unlocked items starting at 600 for blocks and 4096 for items by default. This step has no interractions beyond seeing the results.</div>
  <div class=divider></div>
  <div>The Download step builds a new zip archive of your files and offers a direct link to it. You may then download the archive and extract the modified config files over your old ones to apply the new id's.</div>
  <div class=divider></div>
  <div style='font-size: 18pt; font-weight: bold;'>Drag & Drop file onto grey area or click to browse:</div>
  <div class=note>If no file is selected before uploading, instead of erroring it will display sample data generated from static test files.</div>
  <div>
    <form action='#fromUpload' method='post' enctype='multipart/form-data'>
      <input type=hidden name=step value='analysis' />
      <input type=file name=file style='width: 100%; height: 50px; background-color: lightgray;' /><br>
      <input class=button type=submit value='Upload' />
      <input type=text name=debug placeholder='Debug Level' /> Debug level. (0 - 4) Determines amount of debug output where 0 is none. There will still be normal output though.<br>
    </form>
  </div>
  <!--<div style='font-size: 14pt; font-weight: bold;'>Or</div>
  <div>
  (Not implemented yet) Enter key to access previous file:<br>
  <form action='' method='post'>
    <input type=text name=step placeholder='Key' style='width: 100%;'/>
    <input class=button type=submit value='Submit Key' />
  </form>
  </div>-->
  <div class=divider></div>
  <div style='font-weight: bold;'>Note that certain mods do not use the forge config format! Although not finding any id's in a file will not break anything, the resolver will not be able to change the id's in the file, or take them into account when assigning id's for other files. In forge configs block and item id's are kept within block{} and item{} blocks, these are what the resolver is looking for when extracting id's.
  </div>
  <div class=divider></div>
  <div>Certain mods have other names for the blocks, which means the resolver will not be able to tell that there are id's within without being told so. For example chickenbones wireless redstone mod, it uses several custom blocks with different names that do not indicate whether they contain id's or not. There are also other options mixed in with the id's in these blocks that complicate things further. Thus the wirelessredstone.cfg file is ignored.
  </div>
  <div class=divider></div>
  <div>Also ignored are files with any file name extension other than .cfg or .conf.</div>
  <div>What it also will not do is take into account id's that need to be within a specific range, or have surrounding id's clear. You will have to account for this manually.</div>";

}
}

{
$step = $_POST['step'];
if ($step == 'analysis')
{
  step($step);
  
  echo "
  <head>
    <title>Minecraft ID Resolver - Analysis Step</title>
  </head>";
  
  list($error, $filekey) = recieveFile('file');
  
  if ($error == -1)
  {
    $filekey = "demo";
    $error = 0;
  }
  
  session_start();
  
  if ($error == 0)
  {
    if ($filekey == "demo") echo "<div><div class='demotitle inline'>[ DEMO MODE ]</div><div class='note inline'>[ Displayed data is generated from demo files. To get actual data please upload a zip archive with your configs. ]</div></div>";
    
    echo "
    Your key: <div id=key class=key>" . $filekey . "</div><br>
    Copy this key. Should you be unable to download the archive in the final step this can be used to recover it. You should also include this when reporting bugs.<br>
    <br>
    Now you may overview the id's that were found in the configs you provided.<br>
    You may tick the box before any id to lock it. This will exclude this option from being assigned a new id in the next step as well as exclude the same id from the assigning process.<br>
    <br>
    Here you may specify the starting values at which block and item id's will start being assigned.<br>
    These will override the default values. Leave blank to use the defaults.<br>
    <form action='#fromAnalysis' method=post>
   <input type=text size=40 name=startblock placeholder='Starting block ID (Default: $startblock)' />
   <input type=text size=40 name=startitem placeholder='Starting item ID (Default: $startitem)' /><br>
   <input class=button type='submit' value='Next' />";
    
    
    if ($filekey != "demo")
    {
      $archivepath = "archives/$filekey.zip";
      $targetpath = "extracted/$filekey";
      
      #list($times_read, $configs, $configValues, $names) = readZip($path . $filename, $ignore, 2);
      
      echo "<div id=messages class=noteBox>";
      
      if (!extractZip($archivepath, $targetpath))
        echo "<div class=error>[Error]Something went wrong when trying to extract your archive!</div>";
      
      $dirpath = "extracted/$filekey";
    }
    elseif ($filekey == "demo")
    {
      $dirpath = "demofiles";
    }
    
    unset($levels);
    $entries = myReadDir($dirpath, $search, null, null, 0, $debug);
    
    asort($entries);
    
    #myVarDump($levels);
    
    foreach ($entries as $entriesKey => $entriesValue)
    {
      $config[$entriesKey]['path'] = $entriesValue['path'];
      $config[$entriesKey]['name'] = $entriesValue['name'];
      
      if ($filekey != "demo")
      {
        if (strpos($configValue['path'], "/") == 0)
          $config[$entriesKey]['fullpath'] = "extracted/$filekey/" . $entriesValue['path'];
        else
          $config[$entriesKey]['fullpath'] = "extracted/$filekey" . $entriesValue['path'];
      }
      elseif ($filekey == "demo")
      {
        if (strpos($configValue['path'], "/") == 0)
          $config[$entriesKey]['fullpath'] = "demofiles/" . $entriesValue['path'];
        else
          $config[$entriesKey]['fullpath'] = "demofiles" . $entriesValue['path'];
      }
    }
    
    foreach ($config as $configKey => $configValue)
    {
      $skip = 0;
      if ($debug > 0) echo "<div>[Debug]Reading file " . $configValue['fullpath'] . "</div>";
      $filepath = $configValue['fullpath'];
      
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
        
        if ($debug > 0) echo "<div>[Debug][index]Reading file " . $configValue['name'] . ":</div>";
        
        if (strtolower($compat[$configValue['name']]['preshifted']) == 'yes')
        {
          if ($debug > 0) echo "<div>[Debug][index]" . $configValue['name'] . " is pre-shifted</div>";
          $config[$configKey]['preshifted'] = 'yes';
        }
        else
        {
          if ($debug > 0) echo "<div>[Debug][index]Ignored shift on " . $configValue['name'] . "</div>";
          $config[$configKey]['preshifted'] = 'no';
        }
        
        list($config[$configKey]['values'], $config[$configKey]['idCounter'], $used_ids) = extractValues($config[$configKey]['path'], $config[$configKey]['contents'], $compat, $debug);
        
        if ($config[$configKey]['idCounter'] == 0)
        {
          echo "<div class=warning>[Warning]No id's could be found in " . $configValue['path'] . ". Either there are none, or it contains config blocks with non-standard names! This file probably need a compatibility file!</div>";
          $counter_warnings++;
        }
        elseif ($config[$configKey]['idCounter'] == -1)
        {
          echo "<div class=note>[Note]" . $config[$configKey]['path'] . " has no id's according to compat file.</div>";
          $counter_notes++;
        }
        elseif ($config[$configKey]['idCounter'] == -2)
        {
          echo "<div class=error>[Error]" . $config[$configKey]['path'] . " is known to contain id's but is not supported at the moment and will be ignored!.</div>";
          $counter_errors++;
        }
        else
        {
          echo "<div class=highNote>[Note]Found " . $config[$configKey]['idCounter'] . " id's in " . $config[$configKey]['path'] . "</div>";
          $counter_notes++;
        }
      }
    }
    
    echo "</div><script>toggleHidden(document.getElementById('messages'), null);</script>";
    
    echo "<div class=pnt onClick='toggleHidden(document.getElementById(\"messages\"), null);'>[ ";
    if ($counter_notes >= 1) echo "$counter_notes notes, ";
    if ($counter_warnings >= 1) echo "<o>$counter_warnings warnings</o>, ";
    if ($counter_errors >= 1) echo "<r>$counter_errors errors</r>";
    echo " ] <div class='note inline'>Click to show/hide</div></div>";
    
    foreach ($config as $configValue)
    {
      foreach ($configValue['values'] as $configValueValue)
      {
        if(is_numeric($configValueValue['value']))
          $used_ids[] = array('id' => $configValueValue['value'], 'name' => $configValueValue['id'], 'source' => $configValue['path']);
          $used_id_ranges[] = $configValueValue['value'];
      }
    }
    
    $ranges = getRanges($used_id_ranges);
    
    echo "<div class=used_ids>
    <div class='inline pnt' onClick='toggleHidden(document.getElementById(\"used_ids\"), null)'> [ Used Ids ]</div>
    <div class='inline note pnt' onClick='toggleHidden(document.getElementById(\"used_ids\"), null)'> - Click to show</div>
    <div id=used_ids>";
    foreach ($ranges as $arrayValue)
    {
      if (is_array($arrayValue))
      {
        $ids_in_range = $arrayValue['end'] - $arrayValue['start'];
        $total_ids += $ids_in_range;
        echo "<div class=option>" . $arrayValue['start'] . " - " . $arrayValue['end'] . " (" . $ids_in_range . ")</div>";
      }
      else
      {
        $total_ids += 1;
        echo "<div class=option>" . $arrayValue . "</div>";
      }
    }
    echo "</div>";
    echo "<div class=pnt onClick='toggleHidden(document.getElementById(\"used_ids\"), null)'>Total: $total_ids</div>";
    echo "</div><script>toggleHidden(document.getElementById(\"used_ids\"), null)</script>";
    
    echo "<div class=divider></div>";
    
    foreach ($config as $configKey => $configValue)
      $total_options += $configValue['idCounter'];
    
    echo"<div>A total of <div class='warning inline'>" . count($config) . "</div> files have been scanned and <div class='warning inline'>$total_options</div> configurable id's were found and extracted and are displayed below!</div>";
    
    echo "<div class=divider></div>";
    
    #myVarDump($used_ids);
    
    #echo "<div class=divider></div>";
    
    $_SESSION['debug'] = $debug;
    $_SESSION['config'] = $config;
    $_SESSION['filekey'] = $filekey;
    
    $_SESSION['startblock'] = $startblock;
    $_SESSION['startitem'] = $startitem;
    
    $title_counter = 1;
    $value_counter = 1;
    
    echo "<div id=configs style='border: 1px solid black;'><input type=checkbox id=all onClick='toggleAll(this)'></input><label for=all> Toggle All</label>
    <input type=hidden name=step value='assigning'></input>
    <input type=hidden name=key value='$filekey'></input>";
    foreach ($config as $configKey => $configValue)
    {
      if ($configValue['idCounter'] >= 1)
      {
        $name = $configValue['name'];
        $path = $configValue['path'];
        $values = $configValue['values'];
        if ($configValue['idCounter'] <= 10) 
          $optionHeight = (($configValue['idCounter'] +1) * 21);
        else
          $optionHeight = (11 * 21);
        
        echo "
      <div class=divider style='border-color: orange;'></div>
      <div class='config pnt' onClick='toggleHiddenBlock(this, \"$path\", \"$optionHeight\", null);hideConflicts(\"$name\");'><div class=toggleButton id='$path togglebutton'>+</div><label class=pnt>[" . str_pad($configValue['idCounter'], 3, "0", STR_PAD_LEFT) . " ids] $path </label> </div>
      <div id='" . $path . "' class='configBox overflowing' style='border: 1px dashed gray; height: " . $optionHeightpx . "px;'>
        <div>
          <input type=checkbox id='$name all' class='$name' onClick='toggle(this)'></input><label for='$name all'> Toggle All</label>
          <input type=checkbox id='$name block' class='$name' onClick='toggleType(this, \"Block\")'></input><label for='$name block'> Toggle Blocks </label>
          <input type=checkbox id='$name item' class='$name' onClick='toggleType(this, \"Item\")'></input><label for='$name item'> Toggle Items </label>
        </div>";
        $counter_conflicts_total = 0;
        
        foreach ($values as $valuesKey => $valuesValue)
        {
          $id = trim($valuesValue['id']);
          $type = $valuesValue['type'];
          $idvalue = $valuesValue['value'];
          $nameID = $name . "-" . $id;
          $nameID = str_replace("\"", "", $nameID);
          
          /*
          if ($type == "block")
            $checkboxvalue = $id . "=" . $idvalue . "=" . $path;
          elseif ($type == "item")
          {
            if ($valuesValue['preshifted'] == 'yes')
              $checkboxvalue = $id . "=" . $idvalue . "=" . $path;
            else
              $checkboxvalue = $id . "=" . ($idvalue + $shiftValue) . "=" . $path;
          }*/
          
          $checkboxvalue = $id . "=" . $idvalue . "=" . $path;
          
          $conflicts = find_conflicting_ids($used_ids, $idvalue);
          $conflict = false;
          $counter_conflicts = 0;
          foreach ($conflicts as $conflictsKey => $conflictsValue)
          {
            if ($used_ids[$conflictsValue]['source'] != $path)
            {
              $conflict[] = array('source' => $used_ids[$conflictsValue]['source'], 'name' => $used_ids[$conflictsValue]['name']);
              $counter_conflicts++;
              $counter_conflicts_total++;
            }
          }
          
          if ($type == "block")
          {
            echo "
          <div id=item class=option>
            <div class=inline><input type=checkbox name='id_$value_counter' value='" . $checkboxvalue . "' id='" . $name . $value_counter . "' class='" . $name . "Block'></input><label for='" . $name . $value_counter . "'>$type - $id=$idvalue</label></div>";
            if ($conflict !== false) echo "<div class='inline error lftmrgn'> Conflict with " . $conflict[0]['name'] . " from " . $conflict[0]['source'] . "!</div>";
            if ($counter_conflicts > 1)
            {
              echo "<div class='inline warning lftmrgn pnt' onClick='toggleHidden(document.getElementById(\"$nameID\"), null)'> +" . ($counter_conflicts-1) . " more</div>";
              echo "<div id='$nameID' class=conflictBox>";
              foreach ($conflict as $conflictKey => $conflictValue)
              {
                if ($conflictKey != 0)
                  echo "<div><div class='inline error lftmrgn'>Conflict with " . $conflictValue['name'] . " from " . $conflictValue['source'] . "</div></div>";
              }
              echo "</div>";
              #echo "<script>toggleHidden(document.getElementById(\"$name-$id\"))</script>";
            }
            echo "
          </div>";
          }
          elseif ($type == "item" && $configValue['preshifted'] == 'yes')
          {
            echo "
          <div id=item class=option>
            <div class=inline><input type=checkbox name='id_$value_counter' value='" . $checkboxvalue . "' id='" . $name . $value_counter . "' class='" . $name . "Block'></input><label for='" . $name . $value_counter . "'>$type - $id=$idvalue (in-game: $idvalue)</label></div>";
            if ($conflict !== false) echo "<div class='inline error lftmrgn'> Conflict with " . $conflict[0]['name'] . " from " . $conflict[0]['source'] . "!</div>";
            if ($counter_conflicts > 1)
            {
              echo "<div class='inline warning lftmrgn pnt' onClick='toggleHidden(document.getElementById(\"$nameID\"), null)'> +" . ($counter_conflicts-1) . " more</div>";
              echo "<div id='$nameID' class=conflictBox>";
              foreach ($conflict as $conflictKey => $conflictValue)
              {
                if ($conflictKey != 0)
                  echo "<div><div class='inline error lftmrgn'>Conflict with " . $conflictValue['name'] . " from " . $conflictValue['source'] . "</div></div>";
              }
              echo "</div>";
              #echo "<script>toggleHidden(document.getElementById(\"$name-$id\"))</script>";
            }
            echo "
          </div>";
          }
          elseif ($type == "item")
          {
            echo "
          <div id=item class=option>
            <div class=inline><input type=checkbox name='id_$value_counter' value='" . $checkboxvalue . "' id='" . $name . $value_counter . "' class='" . $name . "Item'></input><label for='" . $name . $value_counter . "'>$type - $id=$idvalue (in-game: " . ($idvalue + $shiftValue) . ")</label></div>";
            if ($conflict !== false) echo "<div class='inline error lftmrgn'> Conflict with " . $conflict[0]['name'] . " from " . $conflict[0]['source'] . "!</div>";
            if ($counter_conflicts > 1)
            {
              echo "<div class='inline warning lftmrgn pnt' onClick='toggleHidden(document.getElementById(\"$nameID\"), null)'> +" . ($counter_conflicts-1) . " more</div>";
              echo "<div id='$nameID' class=conflictBox>";
              foreach ($conflict as $conflictKey => $conflictValue)
              {
                if ($conflictKey != 0)
                  echo "<div><div class='inline error lftmrgn'>Conflict with " . $conflictValue['name'] . " from " . $conflictValue['source'] . "</div></div>";
              }
              echo "</div>";
              #echo "<script>toggleHidden(document.getElementById(\"$name-$id\"))</script>";
            }
            echo "
          </div>";
          }
          
          $value_counter++;
        }
        echo "</div>";
        if ($counter_conflicts_total == 0)
          echo "<div class='pnt config' onClick='toggleHiddenBlock(this, \"$path\", \"$optionHeight\")'>No conflicts found!</div>";
        elseif ($counter_conflicts_total == 1)
          echo "<div class='pnt warning' onClick='toggleHiddenBlock(this, \"$path\", \"$optionHeight\")'>1 conflict found!</div>";
        elseif ($counter_conflicts_total >= ($value_counter / 2))
          echo "<div class='pnt error' onClick='toggleHiddenBlock(this, \"$path\", \"$optionHeight\")'>$counter_conflicts_total conflict found!</div>";
        else
          echo "<div class='pnt warning' onClick='toggleHiddenBlock(this, \"$path\", \"$optionHeight\")'>$counter_conflicts_total conflict found!</div>";
        $title_counter++;
      }
    }
    echo "</form></div>";
  }
  else
  {
    echo "Error " . $error . ": " . $str_error[$error];
  }
  
  echo "<script>toggleConfigs()</script>";
  
  #myVarDump($config);
  #myVarDump($levels);
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
  $filekey = $_SESSION['filekey'];
  
  if ($_POST['startblock'] > 0)
    $startblock = $_POST['startblock'];
  elseif (isset($_SESSION['startblock']))
    $startblock = $_SESSION['startblock'];
  
  if ($_POST['startitem'] > 0)
    $startitem = $_POST['startitem'];
  elseif (isset($_SESSION['startitem']))
    $startitem = $_SESSION['startitem'];
  
  step($step);
  
  echo "
  <head>
    <title>Minecraft ID Resolver - Assign Step</title>
  </head>";
  
  echo "<div id=key class=key>Key: " . $_SESSION['filekey'] . "</div><br><br>";
  
  echo "
  <div>
    You may review the changes made below, then proceed to the final step where your download will be avaliable.
  </div>
  <div>
    <form action='#fromAssigning' method=post>
      <input type=hidden name=step value='download' />
      <input class=button type=submit value=Next />
    </form>
  </div>";
  
  $post_counter = 0;
  foreach ($_POST as $postKey => $postValue)
  {
    if (strpos($postKey, 'id_') !== false)
    {
      #echo "<div class=debug>Lockedloop: $postValue</div>";
      #I:"BlockStartingID"=3000
      list($id, $value, $source) = explode("=", $postValue);
      $locked[] = array('id' => $id, 'value' => $value, 'source' => $source);
    }
    $post_counter++;
  }
  
  echo "<div>Counted $post_counter post entries!</div>";
  echo "<div style='border: 1px solid black; height: 100px; overflow-y: scroll;overflow-x: none;'>";
  myVarDump($_POST);
  echo "</div>";
  
  echo "<div>Counted $post_counter post entries!</div>";
  echo "<div style='border: 1px solid black; height: 100px; overflow-y: scroll;overflow-x: none;'>";
  myVarDump($locked);
  echo "</div>";
  
  #echo "<div style='border: 1px solid black; height: 100px; overflow-y: scroll;overflow-x: none;'>";
  #myVarDump($config);
  #echo "</div>";
  
  #echo "[Debug]Found $post_counter locked items.<br>";
  
  $newblockidcounter = $startblock;
  $newitemidcounter = $startitem;
  
  ### BLOCK ID ASSIGNING ###
  echo "<div class='title pnt' onClick='toggleHidden(document.getElementById(\"blockassign\"), null)'>[Block Assign]</div>
  <div id=blockassign class=blockassign>";
  foreach ($config as $configIndex => $configValue)
  {
    if ($configValue['idCounter'] > 0)
    {
      $counter_block_change = 0;
      $counter_block_conflict = 0;
      $counter_block_locked = 0;
      $counter_block_error = 0;
      
      echo "
      <div class='titleBar pnt' onClick='toggleHiddenBlock(this, \"block_" . $configValue['path'] . "\", null)'>File: " . $configValue['path'] . "</div>
      <div id='block_" . $configValue['path'] . "'>";
      
      foreach ($compat[$configValue['name']]['blockranges'] as $compatIndex => $compatValue)
      {
        $localCompat[$compatValue['key']] = $compatValue['range'];
      }
      
      foreach ($configValue['values'] as $configValueValue)
      {
        $targetValue = trim($configValueValue['id'] . "=" . $configValueValue['value']);
        if ($configValueValue['type'] == "block")
        {
          echo "<div>[blockAssign]Assigning " . $configValueValue['id'] . "</div>";
          if ($debug > 0) echo "<div class=debug>[Debug][blockAssign]Checking for \"" . $targetValue . "\" in locked array!</div>";
          if ($debug > 0) echo "<div class=debug>[Debug][blockAssign]thisOptionLocked check: " . thisOptionLocked($configValueValue['id'], $configValueValue['value'], $configValue['path'], $locked) . "</div>";
          
          if (!thisOptionLocked($configValueValue['id'], $configValueValue['value'], $configValue['path'], $locked))
          {
            $assigned = false;
            
            while ($assigned === false)
            {
              if ($debug > 0) echo "<div class=debug>[Debug][blockAssign]Checking if $newblockidcounter is conflicting with vanilla!</div>";
              if (!in_array($newblockidcounter, $reservedVanillaBlocks))
              {
                if ($debug > 0) echo "<div class=debug>[Debug][blockAssign]Checking if $newblockidcounter is locked!</div>";
                if ($debug > 0) echo "<div class=debug>[Debug][blockAssign]partial_str_in_array check: " . thisIdLocked($newblockidcounter, $locked) . "</div>";
                if (!thisIdLocked($newblockidcounter, $locked))
                {
                  $target = $targetValue;
                  $needle = $configValueValue['id'] . "=" . $newblockidcounter;
                  
                  unset($source);
                  unset($change);
                  if (isset($configValue['newContents']))
                    $source = $configValue['newContents'];
                  else
                    $source = $configValue['contents'];
                  
                  $change = str_replace($target, $needle, $source);
                  
                  #echo "
                  #  <div style='border: 1px solid orange;'>$source</div>
                  #  <div style='border: 1px solid red;'>$change</div>";
                  
                  if ($source == $change)
                  {
                    echo "<div class=error>[blockAssign]Failed to change '$target' to '$needle'</div>";
                    $counter_block_error++;
                  }
                  elseif ($source != $change)
                  {
                    echo "<div>Changed <div class=target>$target</div> to <div class=needle>$needle</div></div>";
                    $counter_block_change++;
                  }
                  else
                  {
                    echo "<div class=error>[blockAssign]Something odd happened here... Please report this!</div>";
                  }
                    
                  $configValue['newContents'] = $change;
                  $config[$configIndex]['newContents'] = $change;
                  
                  $currentKey = trim($configValueValue['id']);
                  
                  $assigned = true;
                  
                  // if ($configValue['name'] == "PortalGun.cfg")
                  // {
                    // echo "<div>Looking for '" . $currentKey . "' in localCompat</div>";
                    // echo "<div>Key: " . key_in_array($currentKey, $localCompat) . "</div>";
                  // }
                  
                  
                  if (key_in_array($currentKey, $localCompat))
                  {
                    if ($debug > 0) echo "<div>[Debug]Increased item id counter by " . ($localCompat[$currentKey] - 1) . "</div>";
                    $newblockidcounter += ($localCompat[$currentKey] - 1);
                  }
                  else
                    $newblockidcounter++;
                }
                else
                {
                  echo "<div class=warning>Ignored locked id $newblockidcounter.</div>";
                  $newblockidcounter++;
                }
              }
              else
              {
                echo "<div class=warning>Ignored id $newblockidcounter, conflicting with vanilla.</div>";
                $newblockidcounter++;
                $counter_block_conflict++;
              }
            }
          }
          else
          {
            echo "<div class=note>Ignored locked option \"$targetValue\"</div>";
            $counter_block_locked++;
          }
        }
        #else
          #echo "Ignored non-block.<br>";
      }
      echo "</div>
      <div class='subBar pnt' onClick='toggleHiddenBlock(this, \"block_" . $configValue['path'] . "\", null)'>";
      if ($counter_block_change > 1) echo "$counter_block_change changes"; elseif ($counter_block_change == 1) echo "1 change";
      if ($counter_block_change > 0 && ($counter_block_conflict > 0 || $counter_block_locked > 0 || $counter_block_error > 0)) echo ", ";
      if ($counter_block_conflict > 1) echo "<div class=warning>$counter_block_conflict conflicts</div>"; elseif ($counter_block_conflict == 1) echo "<div class=warning>1 conflict</div>";
      if ($counter_block_conflict > 0 && ($counter_block_locked > 0 || $counter_block_error > 0)) echo ", ";
      if ($counter_block_locked > 0) echo "<div class=note>$counter_block_locked locked</div>";
      if ($counter_block_conflict > 0 && $counter_block_error > 0) echo ", ";
      if ($counter_block_error > 1) echo "<div class=error>$counter_block_error errors</div>"; elseif ($counter_block_error == 1) echo "<div class=error>1 error</div>";
      echo "</div><script>toggleHidden(document.getElementById('block_" . $configValue['path'] . "'), null)</script>
      <div style='height: 5px;'></div>";
    }
  }
  echo "</div>";
  
  ### ITEM ID ASSIGNING ###
  echo "<div class='title pnt' onClick='toggleHidden(document.getElementById(\"itemassign\"), null)'>[Item Assign]</div>
  <div id=itemassign class=blockassign>";
  foreach ($config as $configIndex => $configValue)
  {
    if ($configValue['idCounter'] > 0)
    {
      echo "
      <div class='titleBar pnt' onClick='toggleHiddenBlock(this, \"item_" . $configValue['path'] . "\", null)'>File: " . $configValue['path'] . "</div>
      <div id='item_" . $configValue['path'] . "'>";
      
      foreach ($compat[$configValue['name']]['itemranges'] as $compatIndex => $compatValue)
      {
        $localCompat[$compatValue['key']] = $compatValue['range'];
      }
      
      $counter_item_change = 0;
      $counter_item_conflict = 0;
      $counter_item_locked = 0;
      $counter_item_error = 0;
      
      foreach ($configValue['values'] as $configValueValue)
      {
        $targetValue = trim($configValueValue['id'] . "=" . $configValueValue['value']);
        if ($configValueValue['type'] == "item")
        {
          echo "<div>[itemAssign]Assigning " . $configValueValue['id'] . "</div>";
          if ($debug > 0) echo "[Debug][itemAssign]Checking for \"" . $targetValue . "\" in locked array!<br>";
          
          if (!thisOptionLocked($configValueValue['id'], $configValueValue['value'], $configValue['path'], $locked))
          {
            $assigned = false;
            
            while ($assigned === false)
            {
              if ($debug > 0) echo "[Debug][itemAssign]Checking if $newitemidcounter is conflicting with vanilla!<br>";
              if (!in_array($newitemidcounter, $reservedVanillaItems))
              {
                if ($debug > 0) echo "<div class=debug>[Debug][itemAssign]Checking if $newitemidcounter is locked!</div>";
                if ($debug > 0) echo "<div class=debug>[Debug][itemAssign]Check: " . thisIdLocked($newitemidcounter, $locked) . "</div>";
                if (!thisIdLocked(($newitemidcounter - $shiftValue), $locked))
                {
                  if ($debug > 0) echo "<div>[Debug][itemAssign]Shift value: " . $configValue['preshifted'] . "</div>";
                  
                  //TARGET
                  $target = $targetValue;
                  
                  //NEEDLE
                  if ($configValue['preshifted'] == 'yes')
                    $newitemid = $newitemidcounter;
                  else
                    $newitemid = $newitemidcounter - $shiftValue;
                  
                  $needle = $configValueValue['id'] . "=" . $newitemid;
                  
                  if (isset($configValue['newContents']))
                    $source = $configValue['newContents'];
                  else
                    $source = $configValue['contents'];
                  
                  unset($change);
                  $change = str_replace($target, $needle, $source);
                  
                  #echo "
                  #  <div style='border: 1px solid orange;'>$source</div>
                  #  <div style='border: 1px solid red;'>$change</div>";
                  
                  if ($source == $change)
                  {
                    echo "<div class=error>[itemAssign]Failed to change '$target' to '$needle'</div>";
                    $counter_item_error++;
                  }
                  elseif ($source != $change)
                  {
                    if ($configValue['preshifted'] == 'yes')
                    {
                      echo "<div>Changed '<div class=target>$target</div>' to '<div class=needle>$needle</div>' (in-game: $newitemid) (Pre-shifted)</div>";
                      $counter_item_change++;
                    }
                    else
                    {
                      echo "<div>Changed '<div class=target>$target</div>' to '<div class=needle>$needle</div>' (in-game: " . ($newitemid + $shiftValue) . ")</div>";
                      $counter_item_change++;
                    }
                  }
                  else
                  {
                    echo "<div class=error>[itemAssign]Something odd happened here... Please report this!</div>";
                    $counter_item_error++;
                  }
                  
                  #echo "<div style='border: 1px solid red;'>[" . nl2br($source) . "]</div>";
                  
                  $currentKey = trim($configValueValue['id']);
                  
                  $configValue['newContents'] = $change;
                  $config[$configIndex]['newContents'] = $change;
                  
                  $assigned = true;
                  // if ($configValue['name'] == "PortalGun.cfg")
                  // {
                    // echo "<div>Looking for '" . $currentKey . "' in localCompat</div>";
                    // echo "<div>Key: " . key_in_array($currentKey, $localCompat) . "</div>";
                  // }
                  
                  #myVarDump($localCompat);
                  
                  
                  if (key_in_array($currentKey, $localCompat))
                  {
                    if ($debug > 0) echo "<div>[Debug][itemAssign]Increased item id counter by " . ($localCompat[$currentKey] - 1) . "</div>";
                    $newitemidcounter += ($localCompat[$currentKey] - 1);
                  }
                  else
                    $newitemidcounter++;
                }
                else
                {
                  echo "<div class=warning>Ignored locked id $newitemidcounter.</div>";
                  $newitemidcounter++;
                }
              }
              else
              {
                echo "<div class=warning>Ignored id conflicting with vanilla.</div>";
                $newitemidcounter++;
                $counter_item_conflict++;
              }
            }
          }
          else
          {
            echo "<div class=note>Ignored locked option. \"$targetValue\"</div>";
            $counter_item_locked++;
          }
        }
        #else
          #echo "Ignored non-item.<br>";
      }
      echo "</div>
      <div class='subBar pnt' onClick='toggleHiddenitem(this, \"item_" . $configValue['path'] . "\", null)'>";
      if ($counter_item_change > 1) echo "$counter_item_change changes"; elseif ($counter_item_change == 1) echo "1 change";
      if ($counter_item_change > 0 && ($counter_item_conflict > 0 || $counter_item_locked > 0 || $counter_item_error > 0)) echo ", ";
      if ($counter_item_conflict > 1) echo "<div class=warning>$counter_item_conflict conflicts</div>"; elseif ($counter_item_conflict == 1) echo "<div class=warning>1 conflict</div>";
      if ($counter_item_conflict > 0 && ($counter_item_locked > 0 || $counter_item_error > 0)) echo ", ";
      if ($counter_item_locked > 0) echo "<div class=note>$counter_item_locked locked</div>";
      if ($counter_item_conflict > 0 && $counter_item_error > 0) echo ", ";
      if ($counter_item_error > 1) echo "<div class=error>$counter_item_error errors</div>"; elseif ($counter_item_error == 1) echo "<div class=error>1 error</div>";
      echo "</div><script>toggleHidden(document.getElementById('item_" . $configValue['path'] . "'), null)</script>
      <div style='height: 5px;'></div>";
    }
  }
  echo "</div>";
  
  #myVarDump($config);

  $_SESSION['config'] = $config;
  $_SESSION['debug'] = $debug;
  $_SESSION['filekey'] = $filekey;
  
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
  
  echo $debug . "<br>";
  step($step);
  
  echo "
  <head>
    <title>Minecraft ID Resolver - Download Step</title>
  </head>";
  
  echo "<div id=key class=key>" . $filekey . "</div>";
  
  if ($filekey != "demo")
  {
    foreach ($config as $configKey => $configValue)
    {
      $filepath = $configValue['fullpath'];
      if ($debug > 0) echo "<div>[Debug]Working in '" . $configValue['fullpath'] . "'</div>";
      
      if (writeToFile($configValue['newContents'], $filepath))
        if ($debug >= 1) echo "<div>[Debug]Success on " . $configValue['path'] . "!</div>";
      else
        if ($debug >= 1) echo "<div class=error>[Debug]Fail on " . $configValue['path'] . "!</div>";
    }
    
    $targetpath = "repacked/$filekey.zip";
    $result = addFiles($filekey, $config, $targetpath, $debug);
    
    rrmdir("extracted/$filekey");
    unlink("archives/$filekey" . ".zip");
    
    $dirpath = "repacked/";
    
    $searchfor[] = ".zip";
    
    $archives = myReadDir($dirpath, $searchfor, null, null, 0);
    
    foreach ($archives as $archivesValue)
    {
      $name = "repacked/" . $archivesValue['name'];
      $datetime = filemtime($name);
      if ($datetime !== false)
        if (($datetime + 86400) < time())
        {
          unlink($name);
          if ($debug > 0) echo "<div>[Debug]Deleted $name.</div>";
        }
    }
    
    if (!$result)
      echo "<div class=error>Error while attempting to archive! Please retry!</div>";
    else
      echo "<div>Archiving succeeded! You will find your file here: <a href='$targetpath'>[DOWNLOAD]</a><br>
      <br>
      Download the file into your config directory (You should make a backup of it first) then right click it and \"Extract here\" (assuming you are using WinRAR) overwrite everything.<br>
      <br>
      Should you need to redownload the file later it will remain for 24h. Use your key in step one to gain access to it, or give access to someone else.</div>";
  }
  elseif ($filekey == "demo")
  {
    echo "<div>No download is provided because no file was uploaded. The data that have been displayed has come from demo files. To get a download please start over and upload a zip archive with configs.</div>";
  }
  
  session_write_close();
}
}

?>
</HTML>

<!-- Start of StatCounter Code for Default Guide -->
<script type="text/javascript">
var sc_project=9503528; 
var sc_invisible=1; 
var sc_security="89632002"; 
var scJsHost = (("https:" == document.location.protocol) ?
"https://secure." : "http://www.");
document.write("<sc"+"ript type='text/javascript' src='" +
scJsHost+
"statcounter.com/counter/counter.js'></"+"script>");
</script>
<noscript><div class="statcounter"><a title="hit counter"
href="http://statcounter.com/" target="_blank"><img
class="statcounter"
src="http://c.statcounter.com/9503528/0/89632002/1/"
alt="hit counter"></a></div></noscript>
<!-- End of StatCounter Code for Default Guide -->
