<?php
function readZip($filepath, $ignore, $debug) //name of target, config ignore array, debug mode true/false
{
  $zip = zip_open($filepath);
  
  $times_read = 0;
  
  while (true)
  {
    $entry = zip_read($zip);
    
    zip_entry_open($zip, $entry, 'r');
    
    if (zip_entry_filesize($entry) == 0) break;
    
    $name = zip_entry_name($entry);
    
    if (stristr($name, '.cfg') && !in_array($name, $ignore))
    {
      if ($debug === true)
      {
        echo "<br>[Debug]Name: " . zip_entry_name($entry) . "<br>";
        echo "[Debug]File size: " . zip_entry_filesize($entry) . " bytes<br>";
      }
      
      $name = str_replace('.cfg', '', $name);
      $entry_cont = zip_entry_read($entry, zip_entry_filesize($entry));
      
      $configs[$name] = $entry_cont;
      
      $line = preg_split('/\n|\r/', $entry_cont, -1, PREG_SPLIT_NO_EMPTY);
      
      $counter = 0;
      $total_counter = 0;
      foreach ($line as $key => $value)
      {
        if (stristr($value, '{'))
        {
          if (stristr($value, 'block'))
          {
            $type = "block";
            if ($debug === true)
              echo "[Debug]!!! Found valid config block! ($value)<br>";
          }
          elseif (stristr($value, 'item'))
          {
            $type = "item";
            if ($debug === true)
              echo "[Debug]!!! Found valid config block! ($value)<br>";
          }
          else
          {
            $type = "invalid";
            if ($debug === true)
              echo "[Debug]Found invalid config block! ($value)<br>";
          }
        }
        
        if ($type != "invalid")
        {
          if (stristr($value, 'I:'))
          {
            if ($debug === true)
              echo "[Debug]" . $key . " => " . $value . "<br>";
            $current = explode('=', $value);
            $configValues[$name][$counter]['type'] = $type;
            $configValues[$name][$counter]['id'] = $current[0];
            $configValues[$name][$counter]['value'] = $current[1];
            $counter++;
          }
        }
        $total_counter++;
      }
      
      if ($debug === true)
      {
        echo "Reached end of file. Found $counter ID's in $total_counter lines!<br>";
        echo "===================<br>";
      }
      
      if ($counter > 0)
      {
        $names[] = array('name' => $name, 'amount' => $counter);
      }
      $times_read++;
    }
    elseif ($debug === true)
      echo "Ignored file $name.<br>===================<br>";
      
  }
  
  if ($debug === true)
    echo "Read $times_read files.";
  
  return array($times_read, $configs, $configValues, $names);
}

//Returns int $times_read, array $configs, array $configValues and array $names
//configs has only one level where key is config file name and value is config contents.
//configValues contain each config file name, these in turn contain each option, which in turn contain the keys 'type', 'id' and 'value'.
//names contain two levels, first is an int key for each config file name, second level contains 'name' of the file and 'amount' of valid keys that were found within.

function recieveFile($filehandle) //name of file in form
{
  if (strlen($_FILES[$filehandle]['name']) < 1)
  {
    return 1; /* No file */
  }
  else
  {
    ##Generate key
    $timestamp = time();
    
    $key = hash('md5', $_FILES[$filehandle]['name'] . time());
    
    ##File handling:
    if (stristr($_FILES[$filehandle]['name'], '.zip'))
    {
      if ($_FILES[$filehandle]['size'] < 200000)
      {
        if ($_FILES[$filehandle]['error'] == 0)
        {
          $filename = $key . '.zip';
          
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
  
  return array($error, $key);
}

function addFile($addpath, $targetpath)
{
  $zip = new ZipArchive;
  $result = $zip->open($targetpath);
  
  if ($result === true)
  {
    $zip->addFile($addpath);
    $zip->close();
    return true;
  }
  else
    return false;
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
?>