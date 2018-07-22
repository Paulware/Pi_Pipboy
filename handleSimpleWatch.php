<html>
<body>
<?php
include "common.inc";
include "common.php";

$MAC = getParam ("MAC");
$Pipboy   = findPipboy ($MAC);
if ($Pipboy) { 
   echo ("MAC: $MAC<br>");
   $Username = $Pipboy ["Username"];
   $Owner   = findUsername ($Username);
   $OwnerId = $Owner ["ID"]; 
   $OwnerHealth = $Owner ["Health"];
   $Health  = n3 ($OwnerHealth);
   $Ammo    = $Owner ["Ammo"];
   echo ("[Username]: [$Username]<br>\n" );
   $Mags     = getReloads($OwnerId);
   $Reloads  = n3($Mags);
   $Stimpaks = getQuantity ($OwnerId, "Stimpak");  // Stimpak
   echo ("Number of stimpaks: $Stimpaks<br>\n" );

   $returnAddress = $Pipboy["IpAddress"]; 
   $ownerAddress  = $Owner["IpAddress"];
      
   $Message = getParam ("Message");
   $Reply = "Unknown response: $Message";
   if ($Message == "start") { 
      $Reply = "{[^Welcome to the      Pipboy Watch Use    Left & Right buttons][begin][begin]}"; 
      
   } else if ($Message == "begin") {
      $name = formatString ($Username,19);
      $Reply = "{[^$name Health:$Health          Reloads: $Reloads][Reload][Reload]}";  
      
   } else if ($Message == "Reload") {
      if ($Reloads == 0) { 
         $Reply = "{[^****Reload Page*****Magazines: $Reloads      No Reloads Remaining][Health][Health]}";
      } else {    
         $Reply = "{[^****Reload Page*****Magazines: $Reloads      Push Right to Reload][Health][DoReload]}";
      }
      
   } else if ($Message == "DoReload") {
      $Reloads = doReload ($OwnerId);
      $Reply = "{[^Ammo: 25            Remaining Mags:$Reloads][Health][Reload]}";
      // Send a message to the device to handle reload
      if ($ownerAddress != "") { // Send message to weapon to reload
         $cmd = "python sendMessage.py $ownerAddress \"reload\"";
         exec($cmd);	     
      }         
   } else if ($Message == "Health") {
      if ($Stimpaks == 0) { 
         $Reply = "{[^No Bandages left    Current Health $Health][Leader1][Leader2]}";
      } else { 
         $Reply = "{[^****Health Page*****Health: $Health/100     Push Right To Apply][Leader1][DoBandages]}";
      }   
   } else if ($Message == "DoBandages") {
      useStimpak ($OwnerId); 
      $Stimpaks = getQuantity ($OwnerId, "Stimpak");       
      $Bandages = n3 ($Stimpaks);
      
      $Reply = "{[^Applied Bandage.....Health: 25/100      Bandages Left:$Bandages ][Leader1][Health]}";
      
   } else if ($Message == "Leader1") {
      $leaders = getLeaders(0);
      $Reply = "{[^$leaders][begin][Leader2]}";            
   } else if ($Message == "Leader2") {
      $leaders = getLeaders(2);
      $Reply = "{[^$leaders][begin][Leader3]}";        
   } else if ($Message == "Leader3") {
      $leaders = getLeaders(5);
      $Reply = "{[^$leaders][begin][Leader1]}";        
   } else {
      $Reply = "{[^Command not         understood: $Message][begin][begin]}";
   }
   if ($returnAddress == "") { 
      echo ("Cannot send message [$Reply] because no returnAddress found<br>\n" );
   } else { 
      $cmd = "python sendMessage.py $returnAddress \"$Reply\"";
      echo ( "<h1>CMD:</h1><br>$cmd<BR>\n");
      exec($cmd);	
   }     
} else {
   echo ("<h1>This $MAC is not in the database yet and not associated with any player</h1>\n" );
} 
?>
</body>
</html>