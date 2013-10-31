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
}

{ ### CONFIG IGNORE LIST
$ignore[] = "forge.cfg";
$ignore[] = "forgeChunkLoading.cfg";
$ignore[] = "multipart.cfg";
$ignore[] = "microblocks.cfg";
$ignore[] = "modstats.cfg";
$ignore[] = "GenerationManager.cfg";
$ignore[] = "invTweaks.cfg";
$ignore[] = "HungerOverhaul.cfg";
$ignore[] = "NEI.cfg";
$ignore[] = "NEIServer.cfg";
$ignore[] = "NEISubset.cfg";
$ignore[] = "Waila.cfg";
$ignore[] = "UniversalElectricity.cfg";
$ignore[] = "InvTweaks.cfg";
$ignore[] = "WirelessRedstone.cfg";
}

{ ### RESERVED ID'S

//This is an array of id's used by vanilla blocks.
$reservedVanillaBlocks = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173);

//Ditto above for items.
$reservedVanillaItems = array(256,257,258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,419,420,421,2256,2257,2258,2259,2260,2261,2262,2263,2264,2265,2266,2267);
}

$startblock = 600;
$startitem = 4096;

$maxBlock = 4095;
$maxItem = 31999;

#list($matches, $config, $names) = readZip("config.zip", $ignore, true);

/* foreach ($names as $key => $value)
{
  echo $key . " => " . $value['name'] . " => " . $value['amount'] . "<br>";
}

foreach ($names as $key => $name)
{
  foreach ($config[$name['name']] as $key2 => $contents)
    echo $contents['id'] . "<br>";
} */

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

{### STEP ONE ###
if (!isset($_POST['step']) || $_POST['step'] == 1)
{
  echo "
  STEP ONE<br>
<form action='' method='post' enctype='multipart/form-data'>

<input type=hidden name=step value=2 />
<input type='file' name='file' />

<input type=submit value='Submit' />

</form>
  ";
}
}

{ ### STEP TWO //'application/x-zip-compressed'
if (@$_POST['step'] == 2)
{
  echo "STEP TWO<br>";
  list($error, $key) = recieveFile('file');
  
  session_start();
  
  if ($error == 0)
  {
    echo "Successfully read archive!<br>
    Your key: <div id=key class=key>" . $key . "</div><br>
    Copy this key. If you accidentally close this window or otherwise is unable to download the file in the last step this key will let you access it.<br>
    <br>
    Now you may overview the id's that were found in the configs you provided.<br>
    You may tick the box before any id to lock it. This will exclude this option from being assigned a new id in the next step as well as exclude the same id from the assigning process.
    <form action='' method=post><br>
    <br>
    Here you may specify the starting values at which block and item id's will start being assigned. Leave blank to use defaults.<br>
    Default block: $startblock. Default item: $startitem<br>
   <input type=text size=20 name=startblock placeholder='Custom starting block ID' />
   <input type=text size=20 name=startitem placeholder='Custom starting item ID' />";
  
    
    $path = "archives/";
    $filename = $key . ".zip";
    $ignore = null;
    
    list($times_read, $configs, $configValues, $names) = readZip($path . $filename, $ignore, false);
    
    $_SESSION['configs'] = $configs;
    $_SESSION['configValues'] = $configValues;
    $_SESSION['names'] = $names;
    
    $_SESSION['startblock'] = $startblock;
    $_SESSION['startitem'] = $startitem;
    
    $title_counter = 1;
    $value_counter = 1;
    
    echo "<div id=configs style='border: 1px solid black;'><input type=checkbox name=all id=all /><label for=all> All </label>
    <input type=hidden name=step value=3 />
    <input type=hidden name=key value='$key' />";
    foreach ($names as $key => $value)
    {
      $name = $value['name'];
      $amount = $value['amount'];
      if ($amount > 0)
      {
        echo "
        <div id=" . $name . " style='border: 1px dashed black;'>
        <div><input type=checkbox name='title_$title_counter' id='title_$title_counter' /><label for='title_$title_counter'>" . $name . "</div>";
        foreach ($configValues[$name] as $key => $value)
        {
          $id = str_replace(' ', '', $value['id']);
          $type = $value['type'];
          $idvalue = $value['value'];
          
          echo "
          <div class=option>
            <input type=checkbox name='$id' value='$idvalue' id='box_$value_counter' /><label for='box_$value_counter'>$type - $id=$idvalue</label>
          </div>";
          $value_counter++;
        }
        echo "</div>";
      }
      $title_counter++;
    }
    
    echo "<input type='submit' value=Submit /></form></div>";
  }
  else
  {
    echo "Error " . $error . ": " . $str_error[$error];
  }
}
}

{ ### STEP THREE
if (@$_POST['step'] == 3)
{
  session_start();

  $configs = $_SESSION['configs'];
  $configValues = $_SESSION['configValues'];
  $names = $_SESSION['names'];
  
  if ($_POST['startblock'] > 0)
    $startblock = $_POST['startblock'];
  elseif (isset($_SESSION['startblock']))
    $startblock = $_SESSION['startblock'];
  
  if ($_POST['startitem'] > 0)
    $startitem = $_POST['startitem'];
  elseif (isset($_SESSION['startitem']))
    $startitem = $_SESSION['startitem'];
  
  echo "STEP THREE<br>";
  
  foreach ($_POST as $key => $value)
  {
    if (stristr($key, 'I:') !== false)
    {
      #I:"BlockStartingID"=3000
      $locked[] = $key . "=" . $value;
    }
  }
  
  foreach ($locked as $testings)
  {
    echo "|" . $testings . "|<br>";
  }
  
  $newblockidcounter = $startblock;
  $newitemidcounter = $startitem;
  
  ### BLOCK ID ASSIGNING ###
  echo "=====Starting block assign!<br>";
  foreach ($configValues as $key => $array)
  {
    foreach ($array as $subkey => $value)
    {
      if ($value['type'] == "block")
      {
        while (true)
        {
          $inarray = $value['id'] . "=" . $value['value'];
          if (!isInArray($inarray, $locked))
          {
            if (!isInArray($newblockidcounter, $reservedVanillaBlocks))
            {
              $target = $value['id'] . "=" . $value['value'];
              $needle = $value['id'] . "=" . $newblockidcounter;
              $configs[$key] = str_replace($target, $needle, $configs[$key]);
              echo "Found match! $target => $needle <br>";
              break;
            }
            #else
              #echo "Ignored vanilla reserved id: $newblockidcounter<br>";
          }
          else
          {
            echo "Ignored locked option.<br>";
            break;
          }
        }
        
        $newblockidcounter++;
      }
      else
        echo "Ignored non-block.<br>";
    }
  }
  
  ### ITEM ID ASSIGNING ###
  echo "=====Starting item assign!<br>";
  foreach ($configValues as $key => $array)
  {
    foreach ($array as $subkey => $value)
    {
      if ($value['type'] == "item")
      {
        while (true)
        {
          $inarray = $value['id'] . "=" . $value['value'];
          if (!isInArray($inarray, $locked))
          {
            if (!isInArray($newitemidcounter, $reservedVanillaItems))
            {
              $target = $value['id'] . "=" . $value['value'];
              $needle = $value['id'] . "=" . $newitemidcounter;
              $configs[$key] = str_replace($target, $needle, $configs[$key]);
              echo "Found match! $target => $needle <br>";
              break;
            }
            #else
              #echo "Ignored vanilla reserved id: $newitemidcounter<br>";
          }
          else
          {
            echo "Ignored locked option.<br>";
            break;
          }
        }
        
        echo "Inrement me?<br>";
        $newitemidcounter++;
      }
      else
        echo "Ignored non-item.<br>";
    }
  }
  
  echo "New configs:<br>";
  foreach ($configs as $key => $value)
  {
    echo $key . " =><br>" . nl2br($value) . "<br>";
  }
  
  session_write_close();
}
}

?>

</HTML>