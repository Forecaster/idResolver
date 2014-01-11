<?php
  if (!isset($step))
    $step = 'mode';

  step($step);
  
  echo "
  <head>
    <title>Minecraft ID Resolver - Mode Step</title>
  </head>";
  
  echo "
  <div class=center>
    <form action='#fromMode' method=post>
      <input type=hidden name=step value='upload' />
      <input class=button2 type=submit value='ID Resolver Mode' />
    </form>
  </div>
  <div class=divider></div>
  <div class=center>
    <form action='' method=post>
      <input class=button2 type=submit value='Biome Resolver Mode (N/A)' disabled style='background-color: gray;'/>
    </form>
  </div>
  <div class=divider></div>
  <div class=center>
    <form action='' method=post>
      <input class=button2 type=submit value='Settings Mode (N/A)' disabled style='background-color: gray;'/>
    </form>
  </div>";
?>