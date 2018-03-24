<html>
<head> <Title>Execute an sql query for reload</Title>
<?php
  include "common.inc";
  include "common.php";
  $OwnerId   = getParam  ("OwnerId");
  $Owner     = findOwner ($OwnerId);
  $Username  = $Owner["Username"];
  $IpAddress = $Owner["IpAddress"];
  
  if ($IpAddress != "") { // Send message to weapon to reload
    $cmd = "python sendMessage.py $IpAddress \"reload\"";
    exec($cmd);	     
  }   
     
  //$sql = "Update pipboys set Ammo=25 Where ID=$OwnerId";    
  //echo ("<br>$sql<br>\n" );
  //$result = query ($sql);  
  echo ("Do reload<br>\n" );
  $Reload = findItem ( "Reload");
  $ReloadId = $Reload ["ID"];  
  modifyInventory ($OwnerId, $ReloadId, 0, -1);
  $numReloads = findInventory ($OwnerId, $ReloadId);
  $reloadQuantity = $numReloads["Quantity"];
  $numBullets = $reloadQuantity * 25;  
  //$sql = "Insert into systemlog (Message) Values ('$Username reloaded')"; 
  //query ($sql);
  echo ("<br>Number of Reloads:$reloadQuantity <br>\n" );
  echo ("<br>Ammo:$numBullets <br>\n" );  
?>
</head>
<body>
  Database has been updated.
<script>
   window.location.href = document.referrer;
</script>
</body>
</html>