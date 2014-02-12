<link rel="stylesheet" type="text/css" href="styles.css"></link>
<?php
#include("inc_functions.php");

?>

<HTML>

      <div style='border: 1px dotted gray;'>
        <div class=pnt><div class=toggleButton>+</div>Compatibility Definitions:</div>
        <div id='" . $path . "_customDefinitions'>
          <div class='inline'>
            <div class=lftmrgn id='" . $path . "_noids_root'>
              <div class='inline'><input type=checkbox value=1></input><label for='" . $path . "_noids'>No Ids</label></div>
            </div>
            
            <div class=lftmrgn>
              <div class='inline'><input type=checkbox value=1 name='" . formNameEncode($path) . ":incompatible' $incompatible id='" . $path . "_incompatible'></input><label for='" . $path . "_incompatible'>Incompatible</label></div>
            </div>
            
            <div class=lftmrgn>
              <div class='inline'><input type=checkbox value=1 name='" . formNameEncode($path) . ":ignore' $ignore id='" . $path . "_ignore'></input><label for='" . $path . "_ignore'>Ignore</label></div>
            </div>
            
            <div class=lftmrgn>
              <div class='inline'><input type=checkbox value=1 name='" . formNameEncode($path) . ":preshifted' value=1 id='" . $path . "_preshifted' $preshifted></input><label for='" . $path . "_preshifted'>Pre-shifted</label></div>
            </div>
            
            <div class='lftmrgn topmrgn'>
              <div class=inputBox style='opacity: 0.55;' onMouseOver='this.style.opacity=\"1.0\"; document.getElementById(\"" . $path . "_desc_blockCat\").style.opacity=\"1.0\";' onMouseOut='this.style.opacity=\"0.55\"; document.getElementById(\"" . $path . "_desc_blockCat\").style.opacity=\"0.55\";'>
                <div>
                  <div class=inline>Block categories:</div><div class='tiny inline pnt lftmrgn'>Clear</div>
                </div>
                <div>
                  <textarea class=compat></textarea>
                </div>
              </div>
            </div>
            
            <div class='lftmrgn topmrgn'>
              <div class=inputBox style='opacity: 0.55;' onMouseOver='this.style.opacity=\"1.0\"; document.getElementById(\"" . $path . "_desc_itemCat\").style.opacity=\"1.0\";' onMouseOut='this.style.opacity=\"0.55\"; document.getElementById(\"" . $path . "_desc_itemCat\").style.opacity=\"0.55\";'>
                <div>
                  <div class=inline>Item categories:</div><div class='tiny inline pnt lftmrgn' onClick='clearCompatabilityDefinition(\"" . $path . "_itemCategories\")'>Clear</div>
                </div>
                <div>
                  <textarea class=compat></textarea>
                </div>
              </div>
            </div>
            
            <div class='lftmrgn topmrgn'>
              <div class=inputBox style='opacity: 0.55;'>
                <div>
                  <div class=inline>Blocks:</div><div class='tiny inline pnt lftmrgn'>Clear</div>
                </div>
                <div>
                  <textarea class=compat></textarea>
                </div>
              </div>
            </div>
            
            <div class='lftmrgn topmrgn'>
              <div class=inputBox style='opacity: 0.55;'>
                <div>
                  <div class=inline>Items:</div><div class='tiny inline pnt lftmrgn'>Clear</div>
                </div>
                <div>
                  <textarea class=compat></textarea>
                </div>
              </div>
            </div>
            
            <div class='lftmrgn topmrgn'>
              <div class=inputBox style='opacity: 0.55;'>
                <div>
                  <div class=inline>Ranges:</div><div class='tiny inline pnt lftmrgn'>Clear</div>
                </div>
                <div>
                  <textarea class=compat></textarea>
                </div>
              </div>
            </div>
            <div class='pnt lftmrgn'>Clear All</div>
          </div>
          <div class='inline' style='border: 1px dotted gray;'>
            <div class=pnt><div class=toggleButton>+</div>File Contents (For reference):</div>
            <div id='" . $path . "_content'>
              <div class='inline pnt tiny'>Maximize</div>
              <div class='inline pnt tiny'>Minimize</div>
              <div><textarea>Content</textarea></div>
            </div>
          </div>
        </div>
      </div>
      <div style='height: 10px;'></div>