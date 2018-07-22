<html>
<body>
<?php
include "common.inc";
include "common.php";

$MAC = getParam ("MAC");
echo ("MAC: $MAC<br>");
$Pipboy = findPipboy ($MAC);
$OwnerId = $Pipboy ["ID"]; 
$MACOwner = $Pipboy ["MACOwner"];

$Owner  = findPipboy ($MACOwner);
$Health = $Owner ["Health"];
$Ammo   = $Owner ["Ammo"];
$MACOwnerId = $Owner["ID"];
echo ("My Owner is: $MACOwner with ID: $MACOwnerId<br>\n" );
$Reloads = getQuantity ($MACOwnerId, "Reload");
$Stimpaks = getQuantity ($MACOwnerId, "Stimpak");  // Stimpak
echo ("Number of stimpaks: $Stimpaks<br>\n" );

$PipboyTypename = $Pipboy["Typename"];
$Username = $Pipboy["Username"];
$IpAddress = $_SERVER['REMOTE_ADDR'];  // Address of sender 
$returnAddress = $Pipboy["IpAddress"]; // Use $IpAddress?
$Message = getParam ("Message");
$Reply = "Unknown response: $Message";
if ($Message == "start") { 
   $Reply = "{[^Welcome to   the Pipboy   Watch Use    Left to      navigate     Right to     engage][begin][begin]}";  
} else if ($Message == "begin") {
   $Reply = "{[^Main Screen   Health:$Health     Ammo: $Ammo][Leader][Leader]}";  
} else if ($Message == "Leader") {
   $leaders = getLeaders();
   $Reply = "{[^Leader Hit Hlth     $leaders][Reload][Reload]}";
} else if ($Message == "Reload") {
   if ($Reloads == 0) { 
      $Reply = "{[^Reload Ammo:$Ammo  Mags: $Reloads     No Reloads Remaining][Health][Health]}";
   } else {    
      $Reply = "{[^Reload Ammo:$Ammo  Mags: $Reloads     Right to     Reload][Health][DoReload]}";
   }   
} else if ($Message == "DoReload") {
   $Reloads = decrementInventory ($MACOwnerId, "Reload"); 
   $Reply = "{[^Reloaded     Remaining    Magazines:$Reloads][Health][Health]}";
} else if ($Message == "Health") {
   if ($Stimpaks == 0) { 
      $Reply = "{[^No Bandages left    Current Health $Health][start][start]}";
   } else { 
      $Reply = "{[^Apply BandageCurrent      Health    $Health Right to     Apply Bandage][begin][DoBandages]}";
   }   
} else if ($Message == "DoBandages") {
   useStimpak ($MACOwnerId); 
   $Owner  = findPipboy ($MACOwner);
   $Health = $Owner ["Health"];   
   $Stimpaks = getQuantity ($MACOwnerId, "Stimpak"); 
   $Reply = "{[^Applying     Bandage      Remaining $Stimpaks  Health:$Health][start][start]}";
} else {
   $Reply = "{[^Command not understood: $Message][start][start]}";
}
$cmd = "python sendMessage.py $returnAddress \"$Reply\"";
echo ( "<h1>CMD:</h1><br>$cmd<BR>\n");
exec($cmd);	
     
?>
</body>
</html>