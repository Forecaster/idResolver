<?php
  
  if ($_POST['startblock'] > 0)
    $startblock = $_POST['startblock'];
  elseif (isset($_SESSION['startblock']))
    $startblock = $_SESSION['startblock'];
  
  if ($_POST['startitem'] > 0)
    $startitem = $_POST['startitem'];
  elseif (isset($_SESSION['startitem']))
    $startitem = $_SESSION['startitem'];
    
  if ($_POST['spaceblock'] > 0)
    $spaceblock = $_POST['spaceblock'];
  
  if ($_POST['spaceitem'] > 0)
    $spaceitem = $_POST['spaceitem'];
    
  if ($_POST['transmit'] == 0 || $_POST['pending'] == 0)
  {
    $compat_form = $_SESSION['compat_form'];
  }
  
  step($step);
  
  echo "
  <head>
    <title>ID Resolver - Assign</title>
  </head>";
  
  echo "<div id=key class=key>Key: " . $filekey . "</div><br><br>";
  
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
  
  $newblockidcounter = $startblock;
  $newitemidcounter = $startitem;
  
  ### BLOCK ID ASSIGNING ###
  echo "<div class='title pnt' onClick='toggleHidden(document.getElementById(\"blockassign\"), null)'>[Block Assign]</div>
  <div id=blockassign class=blockassign>";
  foreach ($config as $configIndex => $configValue)
  {
    $path = $configValue['path'];
    
    if ($configValue['idCounter'] > 0)
    {
      $counter_block_change = 0;
      $counter_block_conflict = 0;
      $counter_block_locked = 0;
      $counter_block_error = 0;
      
      echo "
      <div class='titleBar pnt' onClick='toggleHiddenBlock(this, \"block_" . $path . "\", null)'>File: " . $path . "</div>
      <div id='block_" . $path . "'>";
      
      echo "<div class=debug>Looking for compat for $path</div>";
      
      if ($_POST['standard'] == 1)
      {
        unset($compat_data_result);
        unset($compat_data);
        $query = "SELECT * FROM compatibility WHERE filepath='" . mysqli_real_escape_string($con, $path) . "'";
        $compat_data_result = mysqli_query($con, $query);
        if ($compat_data_result !== false)
          $compat_data = mysqli_fetch_array($compat_data_result);
        else
          die(mysqli_error($con));
      }
      
      if ($_POST['pending'] == 1)
      {
        unset($compat_data_result_secondary);
        unset($compat_data_secondary);
        $query = "SELECT * FROM compatibility_pending WHERE filepath='" . mysqli_real_escape_string($con, $path) . "'";
        $compat_data_result_secondary = mysqli_query($con, $query);
        if ($compat_data_result_secondary !== false)
          $compat_data_secondary = mysqli_fetch_array($compat_data_result_secondary);
        else
          die(mysqli_error($con));
      }
      
      unset($compat_data_form);
      $compat_data_form = $compat_form[$path];
      
      if ($compat_data != null && $compat_data !== false)
        $compatData = true;
      elseif ($compat_data_secondary != null && $compat_data_secondary !== false)
        $compatData = true;
      elseif ($compat_data_form != null)
        $compatData = true;
      else
        $compatData = false;
        
      // echo "<div class=debug>";
      // if ($compat_data != null)
      // {
        // echo "<div>Primary</div>";
        // myVarDump($compat_data);
      // }
      // elseif ($compat_data_secondary != null)
      // {
        // echo "<div>Secondary</div>";
        // myVarDump($compat_data_secondary);
      // }
      // echo "</div>";
        
      /* 
      foreach ($compat[$configValue['name']]['blockranges'] as $compatIndex => $compatValue)
      {
        $localCompat[$compatValue['key']] = $compatValue['range'];
      }
       */
       
      foreach ($configValue['values'] as $configValueValue)
      {
        $targetValue = trim($configValueValue['id'] . "=" . $configValueValue['value']);
        if ($configValueValue['type'] == "block")
        {
          echo "<div>[blockAssign]Assigning " . $configValueValue['id'] . "</div>";
          if ($debug > 0) echo "<div class=debug>[Debug][blockAssign]Checking for \"" . $targetValue . "\" in locked array!</div>";
          if ($debug > 0) echo "<div class=debug>[Debug][blockAssign]thisOptionLocked check: " . thisOptionLocked($configValueValue['id'], $configValueValue['value'], $path, $locked) . "</div>";
          
          if (!thisOptionLocked($configValueValue['id'], $configValueValue['value'], $path, $locked))
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
                  $rangeSet = false;
                  if ($compatData === true)
                  {
                    if ($compat_data['ranges'] != null)
                      $compat_ranges = $compat_data['ranges'];
                    elseif ($compat_data_secondary['ranges'] != null)
                      $compat_ranges = $compat_data_secondary['ranges'];
                    elseif ($compat_data_form['ranges'] != null)
                      $compat_ranges = $compat_data_form['ranges'];
                    
                    if ($compat_ranges != null)
                    {
                      $compat_ranges = str_replace("\n\r", "\n", $compat_ranges);
                      $ranges = explode("\n", $compat_ranges);
                      
                      foreach ($ranges as $value)
                      {
                        list($key, $shift) = explode(':', $value);
                        
                        if ($key == $currentKey)
                        {
                          $newblockidcounter += $shift;
                          $rangeSet = true;
                          break;
                        }
                      }
                    }
                  }
                  
                  if ($rangeSet !== true)
                  {
                    echo "<div class=debug>No range found for $currentKey</div>";
                    $newblockidcounter++;
                  }
                  
                  // if (key_in_array($currentKey, $localCompat))
                  // {
                    // if ($debug > 0) echo "<div>[Debug]Increased item id counter by " . ($localCompat[$currentKey] - 1) . "</div>";
                    // $newblockidcounter += ($localCompat[$currentKey] - 1);
                  // }
                  // else
                    // $newblockidcounter++;
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
      <div class='subBar pnt' onClick='toggleHiddenBlock(this, \"block_" . $path . "\", null)'>";
      if ($counter_block_change > 1) echo "$counter_block_change changes"; elseif ($counter_block_change == 1) echo "1 change";
      if ($counter_block_change > 0 && ($counter_block_conflict > 0 || $counter_block_locked > 0 || $counter_block_error > 0)) echo ", ";
      if ($counter_block_conflict > 1) echo "<div class=warning>$counter_block_conflict conflicts</div>"; elseif ($counter_block_conflict == 1) echo "<div class=warning>1 conflict</div>";
      if ($counter_block_conflict > 0 && ($counter_block_locked > 0 || $counter_block_error > 0)) echo ", ";
      if ($counter_block_locked > 0) echo "<div class=note>$counter_block_locked locked</div>";
      if ($counter_block_conflict > 0 && $counter_block_error > 0) echo ", ";
      if ($counter_block_error > 1) echo "<div class=error>$counter_block_error errors</div>"; elseif ($counter_block_error == 1) echo "<div class=error>1 error</div>";
      echo "</div><script>toggleHidden(document.getElementById('block_" . $path . "'), null)</script>
      <div style='height: 5px;'></div>";
    
      $newblockidcounter += $spaceblock;
    }
  }
  echo "</div>";
  
  ### ITEM ID ASSIGNING ###
  echo "<div class='title pnt' onClick='toggleHidden(document.getElementById(\"itemassign\"), null)'>[Item Assign]</div>
  <div id=itemassign class=blockassign>";
  foreach ($config as $configIndex => $configValue)
  {
    $path = $configValue['path'];
    
    if ($configValue['idCounter'] > 0)
    {
      echo "
      <div class='titleBar pnt' onClick='toggleHiddenBlock(this, \"item_" . $path . "\", null)'>File: " . $path . "</div>
      <div id='item_" . $path . "'>";
      
      foreach ($compat[$configValue['name']]['itemranges'] as $compatIndex => $compatValue)
      {
        $localCompat[$compatValue['key']] = $compatValue['range'];
      }
      
      $counter_item_change = 0;
      $counter_item_conflict = 0;
      $counter_item_locked = 0;
      $counter_item_error = 0;
      
      echo "<div class=debug>Looking for compat for $path</div>";
      
      if ($_POST['standard'] == 1)
      {
        unset($compat_data_result);
        unset($compat_data);
        $query = "SELECT * FROM compatibility WHERE filepath='" . mysqli_real_escape_string($con, $path) . "'";
        $compat_data_result = mysqli_query($con, $query);
        if ($compat_data_result !== false)
          $compat_data = mysqli_fetch_array($compat_data_result);
        else
          die(mysqli_error($con));
      }
      
      if ($_POST['pending'] == 1)
      {
        unset($compat_data_result_secondary);
        unset($compat_data_secondary);
        $query = "SELECT * FROM compatibility_pending WHERE filepath='" . mysqli_real_escape_string($con, $path) . "'";
        $compat_data_result_secondary = mysqli_query($con, $query);
        if ($compat_data_result_secondary !== false)
          $compat_data_secondary = mysqli_fetch_array($compat_data_result_secondary);
        else
          die(mysqli_error($con));
      }
      
      unset($compat_data_form);
      $compat_data_form = $compat_form[$path];
      
      if ($compat_data != null && $compat_data !== false)
        $compatData = true;
      elseif ($compat_data_secondary != null && $compat_data_secondary !== false)
        $compatData = true;
      elseif ($compat_data_form != null)
        $compatData = true;
      else
        $compatData = false;
      
      unset($compat_ranges);
      unset($ranges);
      if ($compat_data['ranges'] != null)
        $compat_ranges = $compat_data['ranges'];
      elseif ($compat_data_secondary['ranges'] != null)
        $compat_ranges = $compat_data_secondary['ranges'];
      elseif ($compat_data_form['ranges'] != null)
        $compat_ranges = $compat_data_form['ranges'];
        
      if ($compatData === true)
      {
        if ($compat_ranges != null)
        {
          $compat_ranges = str_replace("\n\r", "\n", $compat_ranges);
          $ranges = explode("\n", $compat_ranges);
        }
        else
          echo "<div class=debug>No range data for $path</div>";
      }
      else
        echo "<div class=debug>No compat data for $path</div>";
      
      foreach ($configValue['values'] as $configValueValue)
      {
        $targetValue = trim($configValueValue['id'] . "=" . $configValueValue['value']);
        if ($configValueValue['type'] == "item")
        {
          echo "<div>[itemAssign]Assigning " . $configValueValue['id'] . "</div>";
          if ($debug > 0) echo "[Debug][itemAssign]Checking for \"" . $targetValue . "\" in locked array!<br>";
          
          if (!thisOptionLocked($configValueValue['id'], $configValueValue['value'], $path, $locked))
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
                  
                  $rangeSet = false;
                  
                  if ($compatData === true)
                  {
                    foreach ($ranges as $value)
                    {
                      list($key, $shift) = explode(':', $value);
                      echo "<div class=debug>Comparing $key to $currentKey</div>";
                      
                      if ($key == $currentKey)
                      {
                        $newitemidcounter += $shift;
                        $rangeSet = true;
                        break;
                      }
                    }
                  }
                  
                  if ($rangeSet !== true)
                  {
                    if ($compat_ranges != null) echo "<div class=debug>No range found for $currentKey</div>";
                    $newitemidcounter++;
                  }
                  
                  // if (key_in_array($currentKey, $localCompat))
                  // {
                    // if ($debug > 0) echo "<div>[Debug][itemAssign]Increased item id counter by " . ($localCompat[$currentKey] - 1) . "</div>";
                    // $newitemidcounter += ($localCompat[$currentKey] - 1);
                  // }
                  // else
                    // $newitemidcounter++;
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
      <div class='subBar pnt' onClick='toggleHiddenitem(this, \"item_" . $path . "\", null)'>";
      if ($counter_item_change > 1) echo "$counter_item_change changes"; elseif ($counter_item_change == 1) echo "1 change";
      if ($counter_item_change > 0 && ($counter_item_conflict > 0 || $counter_item_locked > 0 || $counter_item_error > 0)) echo ", ";
      if ($counter_item_conflict > 1) echo "<div class=warning>$counter_item_conflict conflicts</div>"; elseif ($counter_item_conflict == 1) echo "<div class=warning>1 conflict</div>";
      if ($counter_item_conflict > 0 && ($counter_item_locked > 0 || $counter_item_error > 0)) echo ", ";
      if ($counter_item_locked > 0) echo "<div class=note>$counter_item_locked locked</div>";
      if ($counter_item_conflict > 0 && $counter_item_error > 0) echo ", ";
      if ($counter_item_error > 1) echo "<div class=error>$counter_item_error errors</div>"; elseif ($counter_item_error == 1) echo "<div class=error>1 error</div>";
      echo "</div><script>toggleHidden(document.getElementById('item_" . $path . "'), null)</script>
      <div style='height: 5px;'></div>";
      
      $newitemidcounter += $spaceitem;
    }
  }
  echo "</div>";
  
  #myVarDump($config);
  
  /*echo "New configs:<br>";
  foreach ($config as $key => $value)
  {
    echo $key . " => " . $value['name'] . "<br>" . nl2br($value['newContents']) . "<br>";
  }*/
?>