<?php
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
?>