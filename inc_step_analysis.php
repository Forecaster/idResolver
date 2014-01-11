<?php
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
?>