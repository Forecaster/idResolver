<?php
function step($currentstep)
{
  $step[1]['name'] = "STEP 1: Upload";
  $step[1]['req'] = "Upload zip file";
  $step[1]['opt'] = "Nothing";
  
  $step[2]['name'] = "STEP 2: Overview";
  $step[2]['req'] = "Nothing";
  $step[2]['opt'] = "Lock ids, change starting id's";
  
  $step[3]['name'] = "STEP 3: Assigning";
  $step[3]['req'] = "Nothing";
  $step[3]['opt'] = "Nothing";
  
  $step[4]['name'] = "STEP 4: Download";
  $step[4]['req'] = "Nothing";
  $step[4]['opt'] = "Download";
  
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

function myReadDir($dirpath, $search, $ignore, $subdir, $debug) #max debug 4
{
  $dirhandle = opendir($dirpath);
  $entry_counter = 0;
  
  while (false !== ($entry = readdir($dirhandle)))
  {
    if ($entry != "." && $entry != "..")
    {
      if ($debug >= 1) echo "[Debug][myReadDir]Now testing \"$entry\":<br>";
      $entrypath = $dirpath . "/" . $entry;
      if (is_dir($entrypath) && !isset($subdir))
      {
        if ($debug >= 2) echo "[Debug][myReadDir]===Reading sub-dir: $entry<br>";
        $newEntries = myReadDir($entrypath, $search, $ignore, $entry);
        if (count($newEntries) != 0)
          $entries = array_merge($entries, $newEntries);
        if ($debug >= 2) echo "[Debug][myReadDir]===Finished reading sub-dir: $entry<br>";
      }
      else
      {
        foreach ($search as $value)
        {
          if ($debug >= 2) echo "[Debug][myReadDir]Checking if $entry is a $value file and not in ignore list!<br>";
          if (stristr($entry, $value) && !in_array($entry, $ignore))
          {
            if ($debug >= 3) echo "[Debug][myReadDir]$entry is a valid file!<br>";
            if (isset($subdir))
            {
              if ($debug >= 4) echo "[Debug][myReadDir]$entry has subdir, inserting into entries array!<br>";
              $entries[$entry_counter]['path'] = $subdir . "/" . $entry;
              $entries[$entry_counter]['name'] = $entry;
            }
            else
            {
              if ($debug >= 4) echo "[Debug][myReadDir]$entry has no subdir, inserting into entries array!<br>";
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

function extractValues($contents, $debug) #max debug 4
{
  $line = preg_split('/\n|\r/', $contents, -1, PREG_SPLIT_NO_EMPTY);
  
  $counter = 0;
  $total_counter = 0;
  foreach ($line as $key => $value)
  {
    if (stristr($value, '{'))
    {
      if (stristr($value, 'block'))
      {
        $type = "block";
        if ($debug >= 3)
          echo "[Debug]!!! Found valid config block! ($value)<br>";
      }
      elseif (stristr($value, 'item'))
      {
        $type = "item";
        if ($debug >= 3)
          echo "[Debug]!!! Found valid config block! ($value)<br>";
      }
      else
      {
        $type = "invalid";
        if ($debug >= 3)
          echo "[Debug]Found invalid config block! ($value)<br>";
      }
    }
    
    if ($type != "invalid")
    {
      if (stristr($value, 'I:'))
      {
        if ($debug >= 4)
          echo "[Debug]" . $key . " => " . $value . "<br>";
        $current = explode('=', $value);
        $configValues[$counter]['type'] = $type;
        $configValues[$counter]['id'] = $current[0];
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
      if ($_FILES[$filehandle]['size'] < 300000)
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
?>