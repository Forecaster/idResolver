<?php
  
  step($step);
  
  echo "
  <head>
    <title>ID Resolver - Compatibility</title>
  </head>";
  
  if (isset($_POST['resubmit']))
  {
    $resubmit = $_POST['resubmit'] + 1;
    
    foreach ($_POST as $postKey => $postValue)
    {
      if ($postKey != "step" && $postKey != "key" && $postKey != "resubmit")
      {
        if (isset($postValue) && $postValue != null)
        {
          list($key, $subkey) = explode(':', $postKey);
          #echo "<div class=debug>Receive compat entry " . formNameDecode($key) . " ($key) [$subkey]</div>";
          $key = formNameDecode($key);
          $compat_form[$key][$subkey] = $postValue;
        }
      }
    }
    #echo "<div class=debug>";
    #myVarDump($compat_form);
    #echo "</div>";
  }
  else
    $resubmit = 0;
    
  #echo "<div class=debug>Resubmit: $resubmit</div>";
  
  if ($filekey == null)
  {
    list($error, $filekey) = recieveFile('file');
    
    if ($error == -1)
    {
      #echo "<div class=debug>No file recieved. Defaulting to demo mode</div>";
      $filekey = "demo";
      $error = 0;
    }
  }
  
  if ($error == 0)
  {
    if ($filekey == "demo") echo "<div><div class='demotitle inline'>[ DEMO MODE ]</div><div class='note inline'>[ Displayed data is generated from demo files. To get actual data please upload a zip archive with your configs. ]</div></div>";
    
    echo "
    <div>Your key: <div id=key class=key>" . $filekey . "</div></div>
    <div class=topmrgn>Copy this key. Should you be unable to download the archive in the final step this can be used to recover it. You should also include this when reporting bugs.</div>";
    
    if ($resubmit == 0)
    {
      echo "
        <div id=description_pre_script class='infoBox topmrgn' style='visibility: hidden'>This first time this page is loaded compatibility data will not be used when scanning the files. After the scan, existing compatibility data will be loaded into the fields for each file and will be used upon re-scan. You can re-scan as many times as you like until the resolver is aware of all the ids you need. then click the \"Next\" button to send the compatibility data to the next step for id extraction.</div>
        <div id=description_pre_noscript class='infoBox topmrgn' style='visibility: visible'>Since you do not have javascript enabled, unfortunately the re-scan feature is unavaliable. Enter/verify the compatibility data and click the \"Next\" button to continue to the next step.</div>
        <script>document.getElementById('description_pre_script').style.visibility='visible'; document.getElementById('description_pre_noscript').style.visibility='hidden';</script>";
    }
    elseif ($resubmit > 0)
    {
      if ($resubmit == 1)
        $suffix = "st";
      elseif ($resubmit == 2)
        $suffix = "nd";
      elseif ($resubmit == 3)
        $suffix = "rd";
      elseif ($resubmit >= 4)
        $suffix = "th";
      echo "<div id=description_post class='infoBox topmrgn'>This is your " . $resubmit . $suffix . " time re-scanning. When the resolver has detected all the ids you need just click the \"Next\" button to proceed to the next step.</div>";
    }
    
    echo "
    <div class=divider></div>
    <div id=logContainer style='border: 1px solid gray;'>
      <div id=logHeader class=pnt style='border-bottom: 1px solid lightgray' onClick='toggleHidden(document.getElementById(\"log\"), null); togglePlusMinusIcon(\"toggleButtonLog\");'><div class=toggleButton id='toggleButtonLog'>+</div>Log</div>
      <div id=log>";
      
    { #Extracting file and setting dirpath
      if ($filekey != "demo")
      {
        $archivepath = "archives/$filekey.zip";
        $targetpath = "extracted/$filekey";
        
        #echo "<div class=debug>" . $archivepath . "</div>";
        
        #list($times_read, $configs, $configValues, $names) = readZip($path . $filename, $ignore, 2);
        
        if (!extractZip($archivepath, $targetpath))
          echo "<div class=error>[Error]Something went wrong when trying to extract your archive!</div>";
        
        $dirpath = "extracted/$filekey";
      }
      elseif ($filekey == "demo")
      {
        #echo "<div class=debug>Loading demo files.</div>";
        $dirpath = "demofiles";
      }
    }
    
    /* ### Start of id scan ### */
    
    unset($levels);
    unset($config);
    $entries = myReadDir($dirpath, $search, array(null), null, 0, $debug);
    
    asort($entries);
    
    #myVarDump($levels);
    
    foreach ($entries as $entriesKey => $entriesValue)
    {
      $config[$entriesKey]['path'] = $entriesValue['path'];
      $config[$entriesKey]['name'] = $entriesValue['name'];
      
      if (strpos($config[$entriesKey]['path'], "/") == 0)
        $config[$entriesKey]['fullpath'] = $dirpath . "/" . $entriesValue['path'];
      else
        $config[$entriesKey]['fullpath'] = $dirpath . $entriesValue['path'];
    }
    
    foreach ($config as $configKey => $configValue)
    {
      $path = $configValue['path'];
      
      if ($resubmit > 0)
      unset($compat_data_result);
      unset($compat_data);
      $query = "SELECT * FROM compatibility WHERE path='" . mysqli_real_escape_string($con, $path) . "'";
      $compat_data_result = mysqli_query($con, $query);
      if ($compat_data_result !== false)
        $compat_data = mysqli_fetch_array($compat_data_result);
      else
        die(mysqli_error($con));
      
      $skip = 0;
      if ($debug > 0) echo "<div>[Debug]Reading file " . $configValue['fullpath'] . "</div>";
      $filepath = $configValue['fullpath'];
      
      $contents = myReadFile($filepath);
      if (!$contents)
      {
        echo "<div class=note>[Note]File " . $path . " is empty! Skipping!</div>";
        unset($config[$configKey]);
        $skip = 1;
      }
      else
        $config[$configKey]['contents'] = $contents;
      
      if ($skip == 0)
      {
        $config[$configKey]['newContents'] = $config[$configKey]['contents'];
        
        if ($debug > 0) echo "<div>[Debug][inc_step_compat]Reading file " . $configValue['name'] . ":</div>";
        
        if (strtolower($compat_data['preshifted']) == 1)
        {
          if ($debug > 0) echo "<div>[Debug][inc_step_compat]" . $configValue['name'] . " is pre-shifted</div>";
          $config[$configKey]['preshifted'] = 'yes';
        }
        else
        {
          if ($debug > 0) echo "<div>[Debug][inc_step_compat]Ignored shift on " . $configValue['name'] . "</div>";
          $config[$configKey]['preshifted'] = 'no';
        }
        
        if ($resubmit == 0)
          $compat = null;
        elseif ($resubmit > 0)
          $compat = $compat_form[$config[$configKey]['path']];
        
        list($config[$configKey]['values'], $config[$configKey]['idCounter'], $used_ids) = extractValues($config[$configKey]['path'], $config[$configKey]['contents'], $compat, $debug);
        
        if ($config[$configKey]['idCounter'] == 0)
        {
          echo "<div class=warning>[Warning]No id's could be found in " . $path . ". Either there are none, or it contains config blocks with non-standard names! This file probably need a compatibility file!</div>";
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
    
    echo "</div><script>toggleHidden(document.getElementById('log'), null);</script>"; #end of id=messages
    
    echo "<div class=pnt onClick='toggleHidden(document.getElementById(\"log\"), null); togglePlusMinusIcon(\"toggleButtonLog\");'>[ ";
    if ($counter_notes >= 1) echo "$counter_notes notes, ";
    if ($counter_warnings >= 1) echo "<o>$counter_warnings warnings</o>, ";
    if ($counter_errors >= 1) echo "<r>$counter_errors errors</r>";
    echo " ] <div class='note inline'>Click to show/hide</div></div>";
    
          
    echo "</div>";
    
    echo "<div id=stuffs>
    <form action='#fromCompat' method=post>
    <input type=hidden name=step id=step value='analysis'></input>
    <input type=hidden name=key value='$filekey'></input>
    <input type=hidden name=resubmit value='$resubmit'></input>";
    
    foreach ($config as $configKey => $configValue)
    {
      $ids = $configValue['idCounter'];
      $path = htmlspecialchars($configValue['path']);
      
      unset($blockCategories);
      unset($itemCategories);
      unset($blocks);
      unset($items);
      unset($ranges);
      
      if ($resubmit == 0)
      {
        unset($compat_data_result);
        unset($compat_data);
        $compatTarget = ltrim(str_replace('config/', '', $path), '/');
        $query = "SELECT * FROM compatibility WHERE path='" . mysqli_real_escape_string($con, $compatTarget) . "'";
        $compat_data_result = mysqli_query($con, $query);
        
        if ($compat_data_result !== false)
          $compat_data = mysqli_fetch_array($compat_data_result);
        else
          die(mysqli_error($con));
        
        if ($compat_data != null && $compat_data !== false)
          $compatFile = true;
        else
          $compatFile = false;
          
        ##compat stuff
        
        if ($compat_data['blockCategories'] != null)
          $blockCategories = $compat_data['blockCategories'];
        
        if ($compat_data['itemCategories'] != null)
          $itemCategories = $compat_data['itemCategories'];
        
        if ($compat_data['blocks'] != null)
          $blocks = $compat_data['blocks'];
        
        if ($compat_data['items'] != null)
          $items = $compat_data['items'];
        
        if ($compat_data['ranges'] != null)
          $ranges = $compat_data['ranges'];
        
        if ($compat_data['preshifted'] == 1)
          $preshifted = "checked";
        else
          $preshifted = "";
        
        if ($compat_data['noids'] == 1)
          $noids = "checked";
        else
          $noids = "";
        
        if ($compat_data['incompatible'] == 1)
          $incompatible = "checked";
        else
          $incompatible = "";
          
        if ($compat_data['ignore'] == 1)
          $ignore = "checked";
        else
          $ignore = "";
      }
      elseif ($resubmit > 0)
      {
        if ($compat_form[$path]['blockCategories'] != null)
          $blockCategories = $compat_form[$path]['blockCategories'];
          
        if ($compat_form[$path]['itemCategories'] != null)
          $itemCategories = $compat_form[$path]['itemCategories'];
        
        if ($compat_form[$path]['blocks'] != null)
          $blocks = $compat_form[$path]['blocks'];
        
        if ($compat_form[$path]['items'] != null)
          $items = $compat_form[$path]['items'];
          
        if ($compat_form[$path]['ranges'] != null)
          $ranges = $compat_form[$path]['ranges'];
        
        if ($compat_form[$path]['preshifted'] != null)
          $preshifted = "checked";
        else
          $preshifted = "";
        
        if ($compat_form[$path]['noids'] != null)
          $noids = "checked";
        else
          $noids = "";
        
        if ($compat_form[$path]['incompatible'] != null)
          $incompatible = "checked";
        else
          $incompatible = "";
          
        if ($compat_form[$path]['ignore'] != null)
          $ignore = "checked";
        else
          $ignore = "";
      }
      
      /*
      if (is_array($compat[$path]))
        $compatFile = true;
      else
        $compatFile = false;
      */
      
      if ($resubmit == 0)
      {
        if ($ids > 0)
        {
          if ($ids == 1) $suffix = "id"; else $suffix = "ids";
          if ($compat_data['noids'] == 1)
          {
            $message = "<div class=warning>According to compatibility data this file has no id's, yet we found $ids $suffix here! You may want to make sure these are not actual block/item ids!</div>";
          }
          elseif ($compat_data['ignore'] == 1)
          {
            $message = "<div class=warning>According to compatability data this file is to be ignored, yet we found $ids $suffix here! You may want to make sure these are not actual block/item ids!</div>";
          }
          elseif ($compat_data['incompatible'] == 1)
          {
            $message = "<div class=warning>According to compatability data this file is incompatible, yet we found $ids $suffix here! You may want to make sure these are not actual block/item ids!</div>";
          }
          else
          {
            $message = "<div class=okay>Found <div class='warning inline'>$ids</div> $suffix here. This is probably fine but you still might want to make sure it's not too few or too many and that they are actual block/item ids.</div>";
          }
        }
        else
        {
          if ($compatFile == true)
          {
            $message = "<div class=okay>We found no id's in this file, but compatibility data was found and the available definitions were entered below. This will probably help us find the right id's in the next step if there are any.</div>";
          }
          else
          {
            $noids = "checked";
            $message = "<div class=warning>We found no id's in this file and no compatibility data was found either. We're assuming this file has no ids. If this is not the case please edit compatibility data below to allow extracting from this file.</div>";
          }
        }
      }
      elseif ($resubmit > 0)
      {
        if ($ids > 0)
        {
          if ($ids == 1) $suffix = "id"; else $suffix = "ids";
          
          if ($compat_form[$path]['noids'] != null)
            $message = "<div class=warning>According to compatibility data this file has no id's, yet we found $ids $suffix here! You may want to make sure these are not actual block/item ids!</div>";
          elseif ($compat_form[$path]['ignore'] != null)
            $message = "<div class=warning>According to compatibility data this file is to be ignored, yet we found $ids $suffix here! You may want to make sure these are not actual block/item ids!</div>";
          elseif ($compat_form[$path]['incompatible'] != null)
            $message = "<div class=warning>According to compatibility data this file is incompatible, yet we found $ids $suffix here! You may want to make sure these are not actual block/item ids!</div>";
          else
            $message = "<div class=okay>Found <div class='warning inline'>$ids</div> $suffix here. This is probably fine but you still might want to make sure it's not too few or too many and that they are actual block/item ids.</div>";
          
            
        }
        else
        {
          if ($compat_form[$path]['noids'] != null)
            $message = "<div class=note>According to compatibility data this file has no id's.</div>";
          elseif ($compat_form[$path]['ignore'] != null)
            $message = "<div class=note>According to compatibility data this file is to be ignored.</div>";
          elseif ($compat_form[$path]['incompatible'] != null)
            $message = "<div class=note>According to compatibility data this file is incompatible.</div>";
          elseif ($compat_form[$path]['blockCategories'] != null || $compat_form[$path]['itemCategories'] != null || $compat_form[$path]['blocks'] != null || $compat_form[$path]['items'] != null)
            $message = "<div class=error>Compatibility data has been defined for this file but no ids could be found! Make sure the compatibility data is entered correctly!</div>";
          else
            $message = "<div class=warning>We found no id's in this file and no compatibility data was found either! Make sure this file is marked properly!</div>";
        }
      }
      
      /*
      if ($compat_data['noids'] == 1)
      {
        $noids = "checked";
        $message = "<div class=note>This file has no id's according to compatibility data!</div>";
      }
      elseif ($compat_data['incompatible'] == 1)
      {
        $incompatible = "checked";
        $message = "<div class=error>Sorry. This file is not supported at this time.</div>";
      }
      elseif ($compat_data['ignore'] == 1)
      {
        $ignore = "checked";
        $message = "<div class=error>This file is to be ignored according to a compatibility data.</div>";
      }
      elseif ($ids > 0)
      {
        if ($ids == 1) $suffix = "id"; else $suffix = "ids";
        $message = "<div>Found <div class='warning inline'>" . $ids . "</div> $suffix here. This is probably fine but you still might want to make sure it's not too few or too many.</div>";
      }
      else
      {
        if ($compatFile == true)
        {
          $message = "<div class=note>We found no id's in this file, but compatibility data was found and the required definitions were entered below. This will probably help us find the right id's in the next step.</div>";
        }
        else
        {
          $noids = "checked";
          $message = "<div class=warning>We found no id's in this file and no compatibility data was found either. We're assuming this file has no ids. If this is not the case please edit compatibility data below to allow extracting from this file.</div>";
        }
      }
      */
      
      echo "<div class=divider></div>";
      
      echo "<div id='" . $path . "' style='border: 1px solid black; background: lightgray;'>
      <div>" . $path . "</div>";
      
      echo $message;
      
      echo "
      <div style='border: 1px dotted gray;'>
        <div class=pnt onClick='toggleHidden(document.getElementById(\"" . $path . "_customDefinitions\"), null); togglePlusMinusIcon(\"" . $path . "_togglebuttonCompat\")'><div class=toggleButton id='" . $path . "_togglebuttonCompat'>+</div>Compatibility Definitions:</div>
        <div id='" . $path . "_customDefinitions'>
        
          <div class=lftmrgn id='" . $path . "_noids_root' onMouseOver='document.getElementById(\"" . $path . "_desc_noids\").className=\"desc\";' onMouseOut='document.getElementById(\"" . $path . "_desc_noids\").className=\"descFaded\";'>
            <div class='inline'><input type=checkbox value=1 name='" . formNameEncode($path) . ":noids' $noids id='" . $path . "_noids' onClick='document.getElementById(\"" . $path . "_noids_root\").className=\"lftmrgn\";'></input><label for='" . $path . "_noids'>No Ids</label></div>
            <div class=descFaded id='" . $path . "_desc_noids'> - For files that do not contain any block or item ids.</div>
          </div>
          
          <div class=lftmrgn onMouseOver='document.getElementById(\"" . $path . "_desc_incompatible\").className=\"desc\";' onMouseOut='document.getElementById(\"" . $path . "_desc_incompatible\").className=\"descFaded\";'>
            <div class='inline'><input type=checkbox value=1 name='" . formNameEncode($path) . ":incompatible' $incompatible id='" . $path . "_incompatible'></input><label for='" . $path . "_incompatible'>Incompatible</label></div>
            <div class=descFaded id='" . $path . "_desc_incompatible'> - For files that do contain ids but use a format that is incompatible with the resolver.</div>
          </div>
          
          <div class=lftmrgn onMouseOver='document.getElementById(\"" . $path . "_desc_ignore\").className=\"desc\";' onMouseOut='document.getElementById(\"" . $path . "_desc_ignore\").className=\"descFaded\";'>
            <div class='inline'><input type=checkbox value=1 name='" . formNameEncode($path) . ":ignore' $ignore id='" . $path . "_ignore'></input><label for='" . $path . "_ignore'>Ignore</label></div>
            <div class=descFaded id='" . $path . "_desc_ignore'> - For files that need to be ignored for other reasons.</div>
          </div>
          
          <div class=lftmrgn onMouseOver='document.getElementById(\"" . $path . "_desc_preshifted\").className=\"desc\";' onMouseOut='document.getElementById(\"" . $path . "_desc_preshifted\").className=\"descFaded\";'>
            <div class='inline'><input type=checkbox value=1 name='" . formNameEncode($path) . ":preshifted' value=1 id='" . $path . "_preshifted' $preshifted></input><label for='" . $path . "_preshifted'>Pre-shifted</label></div>
            <div class=descFaded id='" . $path . "_desc_preshifted'> - For files that use pre-shifted item ids. This will tell the resolver so that it can compensate.</div>
          </div>
          
          <div class='lftmrgn topmrgn'>
            <div class=inputBox style='opacity: 0.55;' onMouseOver='this.style.opacity=\"1.0\"; document.getElementById(\"" . $path . "_desc_blockCat\").style.opacity=\"1.0\";' onMouseOut='this.style.opacity=\"0.55\"; document.getElementById(\"" . $path . "_desc_blockCat\").style.opacity=\"0.55\";'>
              <div>
                <div class=inline>Block categories:</div><div class='tiny inline pnt lftmrgn' onClick='clearCompatabilityDefinition(\"" . $path . "_blockCategories\")'>Clear</div>
              </div>
              <div>
                <textarea class=compat name='" . formNameEncode($path) . ":blockCategories' id='" . $path . "_blockCategories' onClick='noidsWarning(\"$path\");'>" . htmlspecialchars($blockCategories) . "</textarea>
              </div>
            </div>
            <div class=descriptionBox style='opacity: 0.55;' id='" . $path . "_desc_blockCat'>The standard block category begins with the line \"Blocks {\". Some mods use other names for them, like \"BlockIds {\". A block category may only contain block ids. If a category has both block and item ids they need to be defined individually. When adding definitions you should, if possible, include the \"{\" symbol if it is on the same line as the category name. Each category is separated by a new line.</div>
          </div>
          
          <div class='lftmrgn topmrgn'>
            <div class=inputBox style='opacity: 0.55;' onMouseOver='this.style.opacity=\"1.0\"; document.getElementById(\"" . $path . "_desc_itemCat\").style.opacity=\"1.0\";' onMouseOut='this.style.opacity=\"0.55\"; document.getElementById(\"" . $path . "_desc_itemCat\").style.opacity=\"0.55\";'>
              <div>
                <div class=inline>Item categories:</div><div class='tiny inline pnt lftmrgn' onClick='clearCompatabilityDefinition(\"" . $path . "_itemCategories\")'>Clear</div>
              </div>
              <div>
                <textarea class=compat name='" . formNameEncode($path) . ":itemCategories' id='" . $path . "_itemCategories' onClick='noidsWarning(\"$path\");'>" . htmlspecialchars($itemCategories) . "</textarea>
              </div>
            </div>
            <div class=descriptionBox style='opacity: 0.55;' id='" . $path . "_desc_itemCat'>The standard item category begins with the line \"Items {\". Some mods use other names for them, like \"ItemIds {\". An item category may only contain item ids. If a category has both block and item ids they need to be defined individually. When adding definitions you should, if possible, include the \"{\" symbol if it is on the same line as the category name. Each category is separated by a new line.</div>
          </div>
          
          <div class='lftmrgn topmrgn'>
            <div class=inputBox style='opacity: 0.55;' onMouseOver='this.style.opacity=\"1.0\"; document.getElementById(\"" . $path . "_desc_blocks\").style.opacity=\"1.0\";' onMouseOut='this.style.opacity=\"0.55\"; document.getElementById(\"" . $path . "_desc_blocks\").style.opacity=\"0.55\";'>
              <div>
                <div class=inline>Blocks:</div><div class='tiny inline pnt lftmrgn' onClick='clearCompatabilityDefinition(\"" . $path . "_blocks\")'>Clear</div>
              </div>
              <div>
                <textarea class=compat name='" . formNameEncode($path) . ":blocks' id='" . $path . "_blocks' style='height: 75px; width: 300px;' onClick='noidsWarning(\"$path\");'>" . htmlspecialchars($blocks) . "</textarea>
              </div>
            </div>
            <div class=descriptionBox style='opacity: 0.55;' id='" . $path . "_desc_blocks'>This is used for defining individual block ids, like \"idBlockShellConstructor\" from Sync. The definition should contain everything before the \"=\" sign and after the \"I:\" (if present). Each definition is separated by a new line.</div>
          </div>
          
          <div class='lftmrgn topmrgn'>
            <div class=inputBox style='opacity: 0.55;' onMouseOver='this.style.opacity=\"1.0\"; document.getElementById(\"" . $path . "_desc_items\").style.opacity=\"1.0\";' onMouseOut='this.style.opacity=\"0.55\"; document.getElementById(\"" . $path . "_desc_items\").style.opacity=\"0.55\";'>
              <div>
                <div class=inline>Items:</div><div class='tiny inline pnt lftmrgn' onClick='clearCompatabilityDefinition(\"" . $path . "_items\")'>Clear</div>
              </div>
              <div>
                <textarea class=compat name='" . formNameEncode($path) . ":items' id='" . $path . "_items' onClick='noidsWarning(\"$path\");'>" . htmlspecialchars($items) . "</textarea>
              </div>
            </div>
            <div class=descriptionBox style='opacity: 0.55;' id='" . $path . "_desc_items'>This is used for defining individual item ids, like \"idItemSyncCore\" from Sync. The definition should contain everything before the \"=\" sign and after the \"I:\" (if present). Each definition is separated by a new line.</div>
          </div>
          
          <div class='lftmrgn topmrgn'>
            <div class=inputBox style='opacity: 0.55;' onMouseOver='this.style.opacity=\"1.0\"; document.getElementById(\"" . $path . "_desc_ranges\").style.opacity=\"1.0\";' onMouseOut='this.style.opacity=\"0.55\"; document.getElementById(\"" . $path . "_desc_ranges\").style.opacity=\"0.55\";'>
              <div>
                <div class=inline>Ranges:</div><div class='tiny inline pnt lftmrgn' onClick='clearCompatabilityDefinition(\"" . $path . "_ranges\")'>Clear</div>
              </div>
              <div>
                <textarea class=compat name='" . formNameEncode($path) . ":ranges' id='" . $path . "_ranges' onClick='noidsWarning(\"$path\");'>" . htmlspecialchars($ranges) . "</textarea>
              </div>
            </div>
            <div class=descriptionBox style='opacity: 0.55;' id='" . $path . "_desc_ranges'>This is used for mods that use a single item in the config for multiple ids, where you define one id and it will then use that plus the following x number of ids. This is defined using the block/item name followed by : and then the total number of ids that will be occupied, including the starting id.</div>
          </div>
          <div class='pnt lftmrgn' onClick='clearAllCompatabilityDefinitions(\"" . $path . "\")'>Clear All</div>
        </div>
      </div>
      <div style='height: 10px;'></div>
      <div style='border: 1px dotted gray;'>
        <div class=pnt onClick='toggleHidden(document.getElementById(\"" . $path . "_content\"), null); togglePlusMinusIcon(\"" . $path . "_togglebuttonContents\")'><div class=toggleButton id='" . $path . "_togglebuttonContents'>+</div>File Contents (For reference):</div>
        <div id='" . $path . "_content'>
          <div class='inline pnt tiny' onClick='document.getElementById(\"" . $path . "_content_box\").rows=" . (substr_count(nl2br($configValue['contents']), '<br />') +2) . ";'>Maximize</div>
          <div class='inline pnt tiny' onClick='document.getElementById(\"" . $path . "_content_box\").rows=10;'>Minimize</div>
          <div><textarea id='" . $path . "_content_box' class=contents readonly rows=10>" . htmlspecialchars($configValue['contents']) . "</textarea></div>
        </div>
      </div>
    </div>";
      
      echo "</div>
      <script>
        hide(document.getElementById(\"" . $path . "_customDefinitions\"));
        hide(document.getElementById(\"" . $path . "_content\"));
      </script>";
    }
    
    echo "
    <input id=rescan style='visibility: hidden;' class=button type=submit value=Re-scan onClick='document.getElementById(\"step\").value=\"compat\"'></input>
    <script>document.getElementById('rescan').style.visibility = 'visible'</script>
    <input class=button type=submit value=Next></input>
    </form>
    </div>";
  }
  else
  {
    echo "Error " . $error . ": " . $str_error[$error];
  }
?>