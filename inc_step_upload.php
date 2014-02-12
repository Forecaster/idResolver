<?php
  step($step);
  
  echo "
  <head>
    <title>Minecraft ID Resolver - Upload Step</title>
  </head>";
  
  echo "
  <div style='font-size: 24pt; font-weight: bold;'>Archive all your files in the config folder as a <r>[.zip]</r> archive.</div>
  <div style='font-size: 14pt;'>Other archive types will <r>NOT</r> work.</div>
  <div class=divider></div>
  <div class=fileUploadBox>
    <div style='font-size: 18pt; font-weight: bold;'>Drag & Drop a file onto the dark grey area or click to browse:</div>
    <div class=note>If no file is selected before uploading it will display sample data generated from static demo files.</div>
    <div>
      <form action='#fromUpload' method='post' enctype='multipart/form-data'>
        <input type=hidden name=step value='compat' />
        <input type=file name=file style='width: 100%; height: 50px; background-color: lightgray;' /><br>
        <input class=button type=submit value='Upload' />
        <div class='pnt tiny note' style='text-align: right;' onClick='toggleHidden(document.getElementById(\"debug\"), null)'>Debug</div>
        <div id=debug>
          <input type=text name=debug placeholder='Debug Level' /> Debug level. (0 - 4) Determines amount of debug output where 0 is none. There will still be normal output though.
        </div>
        <script>hide(document.getElementById(\"debug\"))</script>
        <div>
          <div>Compatibility Data:</div>
          <div><div class=inline><input type=checkbox name=standard id=standard value=1 checked></input><label for=standard>Standard</label></div> <div class='inline tiny note'>- Includes verified compatibility data</div></div>
          <div><div class=inline><input type=checkbox name=pending id=pending value=1 checked></input><label for=pending>Pending</label></div> <div class='inline tiny note'>- Includes unverified compatability data</div></div>
        </div>
        <div class=divider></div>
        <div>
          <div><div class=inline><input type=checkbox name=transmit id=transmit value=1 checked></input><label for=transmit>Save compatibility data</label></div> <div class='inline tiny note'>- Permits submitting new compatibility data to the database for the benefit of others</div></div>
        </div>
      </form>
    </div>
  </div>
  <div class=divider></div>
  <div style='font-weight: bold;'>Note that certain mods do not use the forge config format! Although not finding any id's in a file will not break anything, the resolver will not be able to change the id's in the file, or take them into account when assigning id's for other files. In forge configs block and item id's are kept within block{} and item{} categories, these are what the resolver is looking for when extracting id's.
  </div>
  <div class=divider></div>
  <div>Certain mods have other names for the categories, which means the resolver will not be able to tell that there are id's within without being told so. For example chickenbones wireless redstone mod, it uses several custom categories with different names that do not indicate whether they contain id's or not. There are also other options mixed in with the id's in these categories that complicate things further. Thus the wirelessredstone.cfg file is ignored.
  </div>
  <div class=divider></div>
  <div>Also ignored are files with any file name extension other than .txt, .cfg or .conf.</div>
  <div>What it also will not do is take into account id's that need to be within a specific range, or have surrounding id's clear. You will have to account for this manually.</div>";
?>