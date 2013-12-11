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
  
  #$dump = str_replace('{', '<br>{', $dump);
  #$dump = str_replace('}', '<br>}', $dump);
  #$dump = str_replace('(', '<br>(', $dump);
  #$dump = str_replace(')', '<br>)', $dump);
  #$dump = str_replace('[', '<br>[', $dump);
  
  echo nl2br($dump);
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

function myReadDir($dirpath, $searchfor, $ignore, $subdir, $level, $debug) #max debug 4
{
  global $indent, $compat, $levels;
  if (isset($subdir)) $indent = $indent + 1;
  if ($debug > 0) echo "<div class=functionOutput>";
  if ($debug > 2 && isset($subdir)) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]Reading sub-dir: " . basename($subdir) . "</div>";
  $dirhandle = opendir($dirpath);
  $entry_counter = 0;
  
  while (false !== ($entry = readdir($dirhandle)))
  {
    if ($entry != "." && $entry != "..")
    {
      if ($debug >= 1) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]Now testing \"$entry\":</div>";
      
      $entrypath = $dirpath . "/" . $entry;
      if (is_dir($entrypath))
      {
        if (isset($compat))
        {
          if ($level == 0)
            $compatTarget = $subdir."/".$entry."/folder.cfg";
          else
          {
            $counter = 1;
            while ($counter != $level)
            {
              $subdir = str_replace(dirname($subdir), '', $subdir);
              $counter++;
            }
            
            if (substr($subdir, 0, 1) == "/")
              $compatTarget = $subdir."/".$entry."/folder.cfg";
            else
              $compatTarget = "/".$subdir."/".$entry."/folder.cfg";
              
            #echo "<div>Level: $level</div>";
            #echo "<div>$subdir</div>";
            #echo "<div>$compatTarget</div>";
          }
          
          if ($debug >= 2) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]Checking $compatTarget against folder ignore!</div>";
          if ($debug >= 2) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]Value: " . $compat[$compatTarget]['ignore'] . "</div>";
        }
        else
        {
          if ($debug >= 1) echo "<div class=warning>[Debug][myReadDir]Compat array not set!</div>";
        }
        
        if (isset($compat) && $compat[$compatTarget]['ignore'] == 'yes')
        {
          echo "<div class=note>[Note]Ignored subdir $entry according to compat!</div>";
        }
        else
        {
          if ($debug >= 2) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]$entry is a directory!</div>";
          $level++;
          $newEntries = myReadDir($entrypath, $searchfor, $ignore, $subdir."/".$entry, $level, $debug);
          if (count($newEntries) != 0)
            if (isset($entries))
              $entries = array_merge($entries, $newEntries);
            else
              $entries = $newEntries;
          $level--;
        }
      }
      else
      {
        if ($debug >= 2) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]$entry is NOT a directory!</div>";
        if ($debug >= 2) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]Checking if $entry is a valid file and not in ignore list!</div>";
        foreach ($searchfor as $value)
        {
          if (stristr($entry, $value) && !in_array($entry, $ignore))
          {
            if ($debug >= 3) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]$entry is a valid file!</div>";
            if (isset($subdir))
            {
              if ($debug >= 4) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]$entry is in a subdir, inserting into entries array as $subdir/$entry!</div>";
              $entries[] = array('path' => ($subdir . "/" . $entry), 'name' => $entry);
            }
            else
            {
              if ($debug >= 4) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]$entry has no subdir, inserting into entries array as $entry!</div>";
              $entries[] = array('path' => $entry, 'name' => $entry);
            }
            
            if ($debug >= 2) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]Current level: $level</div>";
            $levels["$level"] = $levels["$level"] +1;
          }
          elseif (!stristr($entry, $value) && $debug >= 2)
          {
            #echo "[Debug][myReadDir]$entry is not a valid file!<br>";
          }
          elseif ($debug >= 2)
            echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]$entry was ignored!</div>";
        }
      }
    }
    
    $entry_counter++;
  }
  
  if ($debug >= 2 && isset($subdir)) echo "<div style='text-indent: " . ($indent * 10) . "px;'>[Debug][myReadDir]Finished reading sub-dir: " . basename($subdir) . "</div>";
  if ($debug > 0) echo "</div>";
  if (isset($subdir)) $indent = $indent - 1;
  
  return $entries;
}

function extractValues($filename, $contents, $compat, $shift, $debug) #max debug 4
{
  if ($debug > 0) echo "<div>[Debug][extractValues]Recieved shift value $shift</div>";
  global $defaultBlockblocks, $defaultItemblocks;
  $counter = 0;
  $total_counter = 0;

  if ($debug > 0) echo "<div class=functionOutput>";
  if ($debug > 0) echo "<div>[Debug][extractValues]Working file: $filename</div>";
  
  foreach ($compat as $compatKey => $compatValue)
  {
    if (($compatName = stristr($filename, $compatKey)) !== false)
    {
      $foundCompat = 1;
      break;
    }
  }
  
  if ($foundCompat == 1)
    if ($debug > 0) echo "<div>[Debug][extractValues]Found compat file $compatName for $filename!</div>";
  else
    if ($debug > 0) echo "<div>[Debug][extractValues]Found no compat file for $filename!</div>";
  
  if ($compat[$compatName]['ids'] != 'no' && $compat[$compatName]['unsupported'] != 'yes')
  {
    $counter = 0;
    $total_counter = 0;
    
    if (isset($compat[$compatName]['blockblocks']))
    {
       $blockblocks = $compat[$compatName]['blockblocks'];
    }
    else
    {
      if ($debug > 0) echo "<div>[Debug][extractValues]No block block compat. Set default.</div>";
      $blockblocks = $defaultBlockblocks;
    }
    
    if (isset($compat[$compatName]['itemblocks']))
    {
      $itemblocks = $compat[$compatName]['itemblocks'];
    }
    else
    {
      if ($debug > 0) echo "<div>[Debug][extractValues]No item block compat. Set default.</div>";
      $itemblocks = $defaultItemblocks;
    }
    
    if (isset($compat[$compatName]['blocks']))
      $blocks = $compat[$compatName]['blocks'];
      
    if (isset($compat[$compatName]['items']))
      $items = $compat[$compatName]['items'];
   $line = preg_split('/\n|\r/', $contents, -1, PREG_SPLIT_NO_EMPTY);
    
    if ($debug > 0) echo "<div>[Debug][extractValues]Trying individual</div>";
    foreach ($line as $lineKey => $lineValue)
    {
      unset($id);
      unset($key);
      if (!stristr('/', $lineValue) && !stristr('#', $lineValue))
        list($key, $id) = explode('=', $lineValue);
      
      if (isset($id) && is_numeric($id) && $id > 0)
      {
        $key = str_replace('I:', '', $key);
        #if ($debug > 0) echo "<div>[Debug][extractValues]Scanning key: $key</div>";
        
        if (str_in_array($key, $blocks))
        {
          if ($debug >= 4) echo "[Debug][extractValues]Block: " . $lineKey . " => " . $lineValue . "<br>";
          
          $configValues[$counter]['type'] = "block";
          $configValues[$counter]['id'] = $key;
          $configValues[$counter]['value'] = $id;
          
          $counter++;
        }
        elseif (str_in_array($key, $items))
        {
          if ($debug >= 4) echo "[Debug][extractValues]Item: " . $lineKey . " => " . $lineValue . "<br>";
          
          $configValues[$counter]['type'] = "item";
          $configValues[$counter]['id'] = $key;
          $configValues[$counter]['value'] = $id + $shift;
          
          $counter++;
        }
      }
    }
    
    if ($debug > 0) echo "<div>[Debug][extractValues]Trying blocks</div>";
    unset($type);
    
    foreach ($line as $lineKey => $lineValue)
    {
      if (stristr($lineValue, '{'))
      {
        if (str_in_array($lineValue, $blockblocks))
        {
          $type = "block";
          if ($debug >= 3)
            echo "[Debug][extractValues]!!! Found valid config block! ($lineValue)<br>";
        }
        elseif (str_in_array($lineValue, $itemblocks))
        {
          $type = "item";
          if ($debug >= 3)
            echo "[Debug][extractValues]!!! Found valid config block! ($lineValue)<br>";
        }
        else
        {
          $type = "invalid";
          if ($debug >= 4)
            echo "[Debug][extractValues]Ignored invalid config block! ($lineValue)<br>";
        }
      }
      elseif (stristr($lineValue, '}'))
      {
        if ($type == "block" || $type == "item")
        {
          $type = "invalid";
          if ($debug >= 4)
            echo "[Debug][extractValues]Block ended ($lineValue)<br>";
        }
      }
      
      if (isset($type) && $type != "invalid" && !stristr('/', $lineValue) && !stristr('#', $lineValue))
      {
        unset($id);
        unset($key);
        list($key, $id) = explode('=', $lineValue);
        
        if (isset($id) && is_numeric($id) && $id > 0)
        {
          $configValues[$counter]['type'] = $type;
          $configValues[$counter]['id'] = $key;
          
          if ($type == "item")
          {
            #if ($shift > 0)
            #{
            #  if ($debug >= 4) echo "[Debug][extractValues]Item $id (+ $shift): " . $lineKey . " => " . $lineValue . "<br>";
            #  $configValues[$counter]['value'] = ($id + $shift);
            #}
            #else
            {
              if ($debug >= 4) echo "[Debug][extractValues]Item: " . $lineKey . " => " . $lineValue . "<br>";
              $configValues[$counter]['value'] = $id;
            }
          } 
          elseif ($type == "block")
          {
            if ($debug >= 4) echo "[Debug][extractValues]Block: " . $lineKey . " => " . $lineValue . "<br>";
            $configValues[$counter]['value'] = $id;
          }
          
          $counter++;
        }
      }
      $total_counter++;
    }
  }
  elseif ($compat[$compatName]['ids'] == 'no')
    $counter = -1;
  elseif ($compat[$compatName]['unsupported'] == 'yes')
    $counter = -2;
  
  if ($debug > 0) echo "<div>[Debug][extractValues]Found $counter id's!</div>";
  if ($debug > 0) echo "</div>";
  return array($configValues, $counter);
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
      if ($_FILES[$filehandle]['size'] < 1048576)
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

function addFiles($filekey, $addpaths, $targetpath, $debug) #max debug 1
{
  $zip = new ZipArchive;
  
  if ($debug >= 1) echo "[Debug][addFiles]Attempting to open $targetpath<br>";
  if ($zip->open($targetpath, ZIPARCHIVE::CREATE) !== TRUE) {
    return false;
  }
  
  foreach ($addpaths as $key => $value)
  {
    $sourcepath = "extracted/$filekey/" . $value['path'];
    $newname = $value['path'];
    $result = $zip->addFile($sourcepath, $newname);
    
    if ($result === true)
      if ($debug >= 1) echo "[Debug][addFiles]Added $sourcepath to $targetpath<br>";
    else
      if ($debug >= 1) echo "[Debug][addFiles]Failed to add $sourcepath to $targetpath<br>";
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
  
  return $result;
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
    if ($arrayValue == trim($str))
    {
      #echo "<div>[Debug][str_in_array]Value '$str' matches '$arrayValue'</div>";
      return true;
    }
  }
}

function key_in_array($key, $array)
{
  foreach ($array as $arrayKey => $arrayValue)
  {
    #echo "<div class=note>Checking '$key' against '$arrayKey'</div>";
    if ($arrayKey == $key)
      return true;
  }
  
  return false;
}

function readCompat($content, $debug)
{
  if ($debug > 0) echo "<div>";
  $preshifted = 'no';
  $ids = 'yes';
  $currentType = "none";
  
  $line = preg_split('/\n|\r/', $content, -1, PREG_SPLIT_NO_EMPTY);
  
  foreach ($line as $lineKey => $lineValue)
  {
    if ($debug > 0) echo "[Debug][readCompat]Current line: $lineValue<br>";
    if (stristr('-preshifted=yes', $lineValue))
    {
      $preshifted = 'yes';
      if ($debug > 0) echo "[Debug][readCompat]Detected shift<br>";
    }
    
    if (stristr('-blockblocks', $lineValue))
    {
      $currentType = "blockblocks";
      if ($debug > 0) echo "[Debug][readCompat]Type set to blockblock<br>";
    }
    elseif (stristr('-itemblocks', $lineValue))
    {
      $currentType = "itemblocks";
      if ($debug > 0) echo "[Debug][readCompat]Type set to itemblock<br>";
    }
    elseif (stristr('-blocks', $lineValue))
    {
      $currentType = "blocks";
      if ($debug > 0) echo "[Debug][readCompat]Type set to blocks<br>";
    }
    elseif (stristr('-items', $lineValue))
    {
      $currentType = "items";
      if ($debug > 0) echo "[Debug][readCompat]Type set to items<br>";
    }
    elseif (stristr('-blockranges', $lineValue))
    {
      $currentType = "blockranges";
      if ($debug > 0) echo "[Debug][readCompat]Type set to blockranges<br>";
    }
    elseif (stristr('-itemranges', $lineValue))
    {
      $currentType = "itemranges";
      if ($debug > 0) echo "[Debug][readCompat]Type set to itemranges<br>";
    }
    elseif ($currentType == "blockblocks")
    {
      $blockblocks[] = $lineValue;
      if ($debug > 1) echo "[Debug][readCompat]Added blockblock<br>";
    }
    elseif ($currentType == "itemblocks")
    {
      $itemblocks[] = $lineValue;
      if ($debug > 1) echo "[Debug][readCompat]Added itemblock<br>";
    }
    elseif ($currentType == "blocks")
    {
      $blocks[] = $lineValue;
      if ($debug > 1) echo "[Debug][readCompat]Added block<br>";
    }
    elseif ($currentType == "items")
    {
      $items[] = $lineValue;
      if ($debug > 1) echo "[Debug][readCompat]Added item<br>";
    }
    elseif ($currentType == "blockranges")
    {
      $explodeLineValue = explode(':', $lineValue);
      $blockranges[] = array('key' => $explodeLineValue[0], 'range' => $explodeLineValue[1]);
      if ($debug > 1) echo "[Debug][readCompat]Added block range<br>";
    }
    elseif ($currentType == "itemranges")
    {
      $explodeLineValue = explode(':', $lineValue);
      $itemranges[] = array('key' => $explodeLineValue[0], 'range' => $explodeLineValue[1]);
      if ($debug > 1) echo "[Debug][readCompat]Added item range<br>";
    }
    elseif (stristr('-unsupported', $lineValue))
    {
      return "unsupported";
      if ($debug > 0) echo "[Debug][readCompat]Detected unsupported<br>";
    }
    elseif (stristr('-noids', $lineValue))
    {
      return "noids";
      if ($debug > 0) echo "[Debug][readCompat]Detected no ids<br>";
    }
    elseif (stristr('-ignore', $lineValue))
    {
      return "ignore";
      if ($debug > 0) echo "[Debug][readCompat]Detected ignore<br>";
    }
  }
  
  if ($debug > 0) echo "</div>";
  return array($preshifted, $blockblocks, $itemblocks, $blocks, $items, $blockranges, $itemranges);
}

function cleanArray($array)
{
  foreach ($array as $arrayKey => $arrayValue)
  {
    if ($arrayValue != null)
      if (is_int($arrayKey))
        $returnArray[] = $arrayValue;
      else
        $returnArray[$arrayKey] = $arrayValue;
  }
  
  return $returnArray;
}






?>
