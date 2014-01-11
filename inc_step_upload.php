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
  <div>What this does, in brief:</div>
  <div class=divider></div>
  <div>The Analysis step (the first after uploading) will scan the uploaded files, extract all the id's it can, tell you how many it found, which files didn't have any, which files where empty etc, it then sifts through all the found id's and will detect conflicts. You may stop here if you only wished to know if there are any conflicts in your configs. If you wish to proceed you may check the boxes by an item to lock it, preventing the next step from interracting with it.</div>
  <div class=divider></div>
  <div>The Assigning step will, as the name implies, assign new id's to any unlocked items starting at 600 for blocks and 4096 for items by default. This step has no interractions beyond seeing the results.</div>
  <div class=divider></div>
  <div>The Download step builds a new zip archive of your files and offers a direct link to it. You may then download the archive and extract the modified config files over your old ones to apply the new id's.</div>
  <div class=divider></div>
  <div style='font-size: 18pt; font-weight: bold;'>Drag & Drop file onto grey area or click to browse:</div>
  <div class=note>If no file is selected before uploading, instead of erroring it will display sample data generated from static test files.</div>
  <div>
    <form action='#fromUpload' method='post' enctype='multipart/form-data'>
      <input type=hidden name=step value='analysis' />
      <input type=file name=file style='width: 100%; height: 50px; background-color: lightgray;' /><br>
      <input class=button type=submit value='Upload' />
      <input type=text name=debug placeholder='Debug Level' /> Debug level. (0 - 4) Determines amount of debug output where 0 is none. There will still be normal output though.<br>
    </form>
  </div>
  <!--<div style='font-size: 14pt; font-weight: bold;'>Or</div>
  <div>
  (Not implemented yet) Enter key to access previous file:<br>
  <form action='' method='post'>
    <input type=text name=step placeholder='Key' style='width: 100%;'/>
    <input class=button type=submit value='Submit Key' />
  </form>
  </div>-->
  <div class=divider></div>
  <div style='font-weight: bold;'>Note that certain mods do not use the forge config format! Although not finding any id's in a file will not break anything, the resolver will not be able to change the id's in the file, or take them into account when assigning id's for other files. In forge configs block and item id's are kept within block{} and item{} blocks, these are what the resolver is looking for when extracting id's.
  </div>
  <div class=divider></div>
  <div>Certain mods have other names for the blocks, which means the resolver will not be able to tell that there are id's within without being told so. For example chickenbones wireless redstone mod, it uses several custom blocks with different names that do not indicate whether they contain id's or not. There are also other options mixed in with the id's in these blocks that complicate things further. Thus the wirelessredstone.cfg file is ignored.
  </div>
  <div class=divider></div>
  <div>Also ignored are files with any file name extension other than .cfg or .conf.</div>
  <div>What it also will not do is take into account id's that need to be within a specific range, or have surrounding id's clear. You will have to account for this manually.</div>";
?>