<html>
<head> <Title>Execute an sql query and return</Title>
<?php
  include "common.inc";
  include "common.php";
  $OwnerId = getParam ("OwnerId" );
  $Owner = findOwner ( $OwnerId);
  $Username = $Owner["Username"];
  $IpAddress = $Owner["IpAddress"];
  
  $health = $Owner["Health"];
  $health = $health + 25;
  if ($health > 25) {
     $health = 25;
  } 
     
  $sql = "Update pipboys set Health=$health Where ID=$OwnerId";    
  echo ("<br>$sql<br>\n" );
  $result = query ($sql);  
  
  $Stimpak = findItem ( "Stimpak");
  $ItemId = $Stimpak["ID"];
  
  modifyInventory ($OwnerId, $ItemId, 0, -1);
  $numStimPaks = findInventory ($OwnerId, $ItemId);  
  
  $sql = "Insert into systemlog (Message) Values ('$Username used a stimpak!')"; 
  query ($sql);
  $quantity = $numStimPaks["Quantity"];
  echo ("<br>Number of stimpaks:$quantity <br>\n" );
  echo ("<br>Health:$health \n" );
?>
</head>
<body>
  Database has been updated.
<script>
   window.location.href = document.referrer;
</script>
</body>
</html>