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

  $reloads = doReload();  
  $bullets = $reloads * 25;
  echo ("<br>Number of Reloads:$reloads <br>\n" );
  echo ("<br>Ammo:$bullets <br>\n" );  
?>
</head>
<body>
  Database has been updated.
<script>
   window.location.href = document.referrer;
</script>
</body>
</html>