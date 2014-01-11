<?php
  session_start();
  $debug = $_SESSION['debug'];
  $config = $_SESSION['config'];
  $filekey = $_SESSION['filekey'];
  
  echo $debug . "<br>";
  step($step);
  
  echo "
  <head>
    <title>Minecraft ID Resolver - Download Step</title>
  </head>";
  
  echo "<div id=key class=key>" . $filekey . "</div>";
  
  if ($filekey != "demo")
  {
    foreach ($config as $configKey => $configValue)
    {
      $filepath = $configValue['fullpath'];
      if ($debug > 0) echo "<div>[Debug]Working in '" . $configValue['fullpath'] . "'</div>";
      
      if (writeToFile($configValue['newContents'], $filepath))
        if ($debug >= 1) echo "<div>[Debug]Success on " . $configValue['path'] . "!</div>";
      else
        if ($debug >= 1) echo "<div class=error>[Debug]Fail on " . $configValue['path'] . "!</div>";
    }
    
    $targetpath = "repacked/$filekey.zip";
    $result = addFiles($filekey, $config, $targetpath, $debug);
    
    rrmdir("extracted/$filekey");
    unlink("archives/$filekey" . ".zip");
    
    $dirpath = "repacked/";
    
    $searchfor[] = ".zip";
    
    $archives = myReadDir($dirpath, $searchfor, null, null, 0);
    
    foreach ($archives as $archivesValue)
    {
      $name = "repacked/" . $archivesValue['name'];
      $datetime = filemtime($name);
      if ($datetime !== false)
        if (($datetime + 86400) < time())
        {
          unlink($name);
          if ($debug > 0) echo "<div>[Debug]Deleted $name.</div>";
        }
    }
    
    if (!$result)
      echo "<div class=error>Error while attempting to archive! Please retry!</div>";
    else
      echo "<div>Archiving succeeded! You will find your file here: <a href='$targetpath'>[DOWNLOAD]</a><br>
      <br>
      Download the file into your config directory (You should make a backup of it first) then right click it and \"Extract here\" (assuming you are using WinRAR) overwrite everything.<br>
      <br>
      Should you need to redownload the file later it will remain for 24h. Use your key in step one to gain access to it, or give access to someone else.</div>";
  }
  elseif ($filekey == "demo")
  {
    echo "<div>No download is provided because no file was uploaded. The data that have been displayed has come from demo files. To get a download please start over and upload a zip archive with configs.</div>";
  }
  
  session_write_close();
?>