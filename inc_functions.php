<?php
function step($currentstep)
{
  $step['mode']['name'] = "STEP: Mode";
  $step['mode']['req'] = "Select mode";
  $step['mode']['opt'] = "Nothing";
  
  $step['upload']['name'] = "STEP: Upload";
  $step['upload']['req'] = "Upload zip file";
  $step['upload']['opt'] = "Nothing";
  
  $step['overview']['name'] = "STEP: Overview";
  $step['overview']['req'] = "Nothing";
  $step['overview']['opt'] = "Lock ids, change starting id's";
  
  $step['assigning']['name'] = "STEP: Assigning";
  $step['assigning']['req'] = "Nothing";
  $step['assigning']['opt'] = "Nothing";
  
  $step['download']['name'] = "STEP: Download";
  $step['download']['req'] = "Nothing";
  $step['download']['opt'] = "Download";
  
  echo "<Table style='border: 1px solid gray;'>";
  foreach ($step as $key => $value)
  {
    if ($key == $currentstep) $class = "step"; else $class = null;
    
    echo "
    <TR>
      <TD class=$class>" . $value['name'] . "</TD>
      <TD class=$class> | Required for this step: " . $value['req'] . "</TD>
      <TD class=$class> | Optional for this step: " . $value['opt'] . "</TD>
    </TR>";
  }
  echo "</Table>";
}

function myVarDump($array)
{
  echo "<br><br>";
  $dump = print_r($array, true);
  
  $dump = str_replace('{', '<br>{', $dump);
  $dump = str_replace('}', '<br>}', $dump);
  $dump = str_replace('(', '<br>(', $dump);
  $dump = str_replace(')', '<br>)', $dump);
  $dump = str_replace('[', '<br>[', $dump);
  
  echo $dump;
}

function myReadFile($filepath)
{
  $filehandle = fopen($filepath, 'r');
  
  $contents = fread($filehandle, filesize($filepath));
  
  if ($contents == null)
    return false;
  else
    return $contents;
}

function myReadDir($dirpath, $searchfor, $ignore, $subdir, $debug) #max debug 4
{
  $dirhandle = opendir($dirpath);
  $entry_counter = 0;
  
  while (false !== ($entry = readdir($dirhandle)))
  {
    if ($entry != "." && $entry != "..")
    {
      if ($debug >= 1) echo "[Debug][myReadDir]Now testing \"$entry\":<br>";
      
      $entrypath = $dirpath . "/" . $entry;
      if ($debug >= 2) echo "[Debug][myReadDir]Testing if $entrypath is a directory<br>";
      if (is_dir($entrypath))
      {
        if ($debug >= 2) echo "[Debug][myReadDir]===Reading sub-dir: $entry<br>";
        $newEntries = myReadDir($entrypath, $searchfor, $ignore, $subdir."/".$entry, $debug);
        if (count($newEntries) != 0)
          if (isset($entries))
            $entries = array_merge($entries, $newEntries);
          else
            $entries = $newEntries;
        if ($debug >= 2) echo "[Debug][myReadDir]===Finished reading sub-dir: $entry<br>";
      }
      else
      {
        foreach ($searchfor as $value)
        {
          if ($debug >= 2) echo "[Debug][myReadDir]Checking if $entry is a $value file and not in ignore list!<br>";
          if (stristr($entry, $value) && !in_array($entry, $ignore))
          {
            if ($debug >= 3) echo "[Debug][myReadDir]$entry is a valid file!<br>";
            if (isset($subdir))
            {
              if ($debug >= 4) echo "[Debug][myReadDir]$entry has subdir, inserting into entries array as $subdir/$entry!<br>";
              $entries[$entry_counter]['path'] = $subdir . "/" . $entry;
              $entries[$entry_counter]['name'] = $entry;
            }
            else
            {
              if ($debug >= 4) echo "[Debug][myReadDir]$entry has no subdir, inserting into entries array as $entry!<br>";
              $entries[$entry_counter]['path'] = $entry;
              $entries[$entry_counter]['name'] = $entry;
            }
          }
          elseif (!stristr($entry, $value) && $debug >= 2)
            echo "[Debug][myReadDir]$entry is not a valid file!<br>";
          elseif ($debug >= 2)
            echo "[Debug][myReadDir]$entry was ignored!<br>";
        }
      }
    }
    
    $entry_counter++;
  }
  
  return $entries;
}

function extractValues($contents, $blockblocks, $itemblocks, $shift, $debug) #max debug 4
{
  if (!is_array($blockblocks) || !is_array($itemblocks))
  {
    echo "[Error]No search arrays recieved! Will not find anything!<br>";
    break;
  }

  $line = preg_split('/\n|\r/', $contents, -1, PREG_SPLIT_NO_EMPTY);
  
  $counter = 0;
  $total_counter = 0;
  foreach ($line as $key => $lineValue)
  {
    if (stristr($lineValue, '{'))
    {
      if (str_in_array($lineValue, $blockblocks))
      {
        $type = "block";
        if ($debug >= 3)
          echo "[Debug]!!! Found valid config block! ($lineValue)<br>";
      }
      elseif (str_in_array($lineValue, $itemblocks))
      {
        $type = "item";
        if ($debug >= 3)
          echo "[Debug]!!! Found valid config block! ($lineValue)<br>";
      }
      else
      {
        $type = "invalid";
        if ($debug >= 4)
          echo "[Debug]Found invalid config block! ($lineValue)<br>";
      }
    }
    
    if ($type != "invalid")
    {
      if (stristr($lineValue, 'I:'))
      {
        if ($debug >= 4)
          echo "[Debug]" . $key . " => " . $lineValue . "<br>";
        $current = explode('=', $lineValue);
        $configValues[$counter]['type'] = $type;
        $configValues[$counter]['id'] = $current[0];
        
        if ($type == "item")
          $configValues[$counter]['value'] = $current[1] + $shift;
        else
          $configValues[$counter]['value'] = $current[1];
        
        $counter++;
      }
    }
    $total_counter++;
  }
  
  return $configValues;
}

function recieveFile($filehandle) //name of file input
{
  if (strlen($_FILES[$filehandle]['name']) < 1)
  {
    return 1; /* No file */
  }
  else
  {
    ##Generate filekey
    $timestamp = time();
    
    $filekey = hash('md5', $_FILES[$filehandle]['name'] . time());
    
    ##File handling:
    if (stristr($_FILES[$filehandle]['name'], '.zip'))
    {
      if ($_FILES[$filehandle]['size'] < 524288)
      {
        if ($_FILES[$filehandle]['error'] == 0)
        {
          $filename = $filekey . '.zip';
          
          if (!file_exists('archives/' . $filename))
          {
            $move = move_uploaded_file($_FILES[$filehandle]['tmp_name'], 'archives/' . $filename);
            
            if ($move===true)
            {
              $error = 0;
            }
            else
              $error = 5; /* Failed to move file */
          }
          else
          {
            $error = 4; /* File exist */
          }
        }
        else
        {
          $error = 3; /* File error */
        }
      }
      else
      {
        $error = 2; /* File to large */
      }
    }
    else
    {
      $error = 1; /* Invalid file type */
    }
  }
  
  return array($error, $filekey);
}

function extractZip($filepath, $targetpath) //name of target, extract to path
{
  $zip = new ZipArchive;
  
  if ($zip->open($filepath) === true)
  {
    if ($zip->extractTo($targetpath) === true)
    {
      $zip->close();
      return true;
    }
    else
    {
      $zip->close();
      return false;
    }
  }
  else
    return false;
}

#DEPRECATED
function addFile($addpath, $targetpath, $newname)
{
  $zip = new ZipArchive;
  
  if ($zip->open($targetpath, ZIPARCHIVE::CREATE) !== TRUE) {
    return false;
  }
  
  $zip->addFile($addpath, $newname);
  $zip->close();
  return true;
}

function addFiles($filekey, $addpaths, $targetpath, $debug) #max debug 1
{
  $zip = new ZipArchive;
  
  if ($debug >= 1) echo "[Debug]Attempting to open $targetpath<br>";
  if ($zip->open($targetpath, ZIPARCHIVE::CREATE) !== TRUE) {
    return false;
  }
  
  foreach ($addpaths as $key => $value)
  {
    $sourcepath = "extracted/$filekey/" . $value['path'];
    $newname = $value['path'];
    if ($debug >= 1) echo "[Debug]Attempting to add $sourcepath to $targetpath<br>";
    $zip->addFile($sourcepath, $newname);
  }
  $zip->close();
  return true;
}

function isInArray($string, $array)
{
  $string = str_replace(' ', '', $string);
  foreach ($array as $value)
  {
    if ($value == $string)
    {
      return true;
    }
  }
  
  return false;
}

function writeToFile($string, $filepath)
{
  $filehandle = fopen($filepath, 'w');
  
  if ($result === false)
    return false;
  
  $result = fwrite($filehandle, $string);
  
  if ($result !== false)
    return true;
  else
    return false;
}

function rrmdir($dir)
{
  foreach(glob($dir . '/*') as $file) {
    if(is_dir($file))
      rrmdir($file);
    else
      unlink($file);
  }
  rmdir($dir);
}

function str_in_array($str, $array)
{
  foreach ($array as $arrayValue)
  {
    if (stristr($str, $arrayValue))
      return true;
  }
}






?>