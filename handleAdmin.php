<html>
<body>
<?php
include "common.inc";
include "common.php";

$MAC = getParam ("MAC");
echo ("MAC: $MAC<br>");
$Pipboy = findPipboy ($MAC);
$OwnerId = $Pipboy ["ID"]; 
$PipboyTypename = $Pipboy["Typename"];
$Username = $Pipboy["Username"];
$IpAddress = $_SERVER['REMOTE_ADDR'];  // Address of sender 
$returnAddress = $Pipboy["IpAddress"]; // Use $IpAddress?
$Message = getParam ("Message");
$Reply = "Unknown response: $Message";
if ($Message == "start") { 
   $Reply = "{[^Pipboy Admin        Configure Game][begin][begin]}";  
} else if ($Message == "begin") {
   $Reply = "{[^Start Game][config][startGame]}";
} else if ($Message == "startGame") {
   TDM();        
   $players = activePlayers();
   if ($players == "") { 
      $Reply = "{[^No Active Players][start][start]}"; 
   } else {
      $Reply = "{[^Game is now started for $players][start][start]}"; 
   }   
} else if ($Message == "config") {
   $Reply = "{[^Games][setTime][Game0]}";  
} else if ($Message == "Game0") {
   $Reply = "{[^Team Death Match][Game1][selectTDM]}";
} else if ($Message == "selectTDM") {
   updateNameValue ("game", "Team Death Match");
   $gameValue = readNameValue ("game");
   echo ("got a gameValue: $gameValue<br>\n" );
   $Reply = "{[^$gameValue has been selected][begin][begin]";
} else if ($Message == "Game1") {
   $Reply = "{[^Capture flag][Game2][selectCaptureFlag]}";
} else if ($Message == "selectCaptureFlag") {
   updateNameValue ("game", "Capture the Flag");
   $gameValue = readNameValue ("game");
   echo ("got a gameValue: $gameValue<br>\n" );
   $Reply = "{[^$gameValue has been selected][begin][begin]";
} else if ($Message == "Game2") {
   $Reply = "{[^Trouble in Terrorist Town][Game3][selectTTT]}";
} else if ($Message == "selectTTT") {
   updateNameValue ("game", "Trouble in Terrorist Town");
   $gameValue = readNameValue ("game");
   echo ("got a gameValue: $gameValue<br>\n" );
   $Reply = "{[^$gameValue has been selected][begin][begin]";
} else if ($Message == "Game3") {
   $Reply = "{[^Protect the VIP][selectVIP][begin]}";
} else if ($Message == "selectVIP") {
   updateNameValue ("game", "Protect the VIP");
   $gameValue = readNameValue ("game");
   echo ("got a gameValue: $gameValue<br>\n" );
   $Reply = "{[^$gameValue has been selected][begin][begin]";
} else if ($Message == "setTime") {
   $minutes = readNameValue ("gameTime"); 
   $Reply = "{[^Increase or decrease game time currentlyset to $minutes min][setUnlimitedAmmo][increaseTime]}";
} else if ($Message == "increaseTime") {
   $Reply = "{[^Increase time][decreaseTime][selectIncreaseTime]}";
} else if ($Message == "selectIncreaseTime") {
   $minutes = modNameValue ("gameTime",1,60,1); 
   $Reply = "{[^The game will last $minutes minutes][increaseTime][increaseTime]}";
} else if ($Message == "decreaseTime") {
   $Reply = "{[^Decrease time][setUnlimitedAmmo][selectDecreaseTime]}";
} else if ($Message == "selectDecreaseTime") {
   $minutes = modNameValue ("gameTime",1,60,-1); 
   $Reply = "{[^The game will last $minutes minutes][decreaseTime][decreaseTime]}";
} else if ($Message == "setUnlimitedAmmo") {
   $unlimitedAmmo = readNameValue ("unlimitedAmmo"); 
   if ($unlimitedAmmo == "True") {    
     $Reply = "{[^Set Unlimited Ammo   Off (currently On)][setUnlimitedHealth][toggleUnlimitedAmmo]}";
   } else {
     $Reply = "{[^Set Unlimited Ammo   On (currently Off)][setAmmo][toggleUnlimitedAmmo]}";
   }
} else if ($Message == "toggleUnlimitedAmmo") {
   $unlimitedAmmo = readNameValue ("unlimitedAmmo"); 
   if ($unlimitedAmmo == "True") {    
     updateNameValue ("unlimitedAmmo", "False");
     $Reply = "{[^Unlimited Ammo Off][setAmmo][setUnlimitedAmmo]}";
   } else {
     updateNameValue ("unlimitedAmmo", "True");
     $Reply = "{[^Unlimited Ammo On][setUnlimitedHealth][setUnlimitedAmmo]}";
   }
} else if ($Message == "setAmmo") {
   $ammo = readNameValue ("ammo");  
   $Reply = "{[^Increase or decrease the start Ammo,    currently: $ammo][setUnlimitedHealth][increaseAmmo]}";
} else if ($Message == "increaseAmmo") {
   $Reply = "{[^Increase the Start  Ammo][decreaseAmmo][selectIncreaseAmmo]}";
} else if ($Message == "selectIncreaseAmmo") {
   $ammo = modNameValue ("ammo",1,1000,25);  
   $Reply = "{[^Each player will start with $ammo rounds][increaseAmmo][increaseAmmo]}";
} else if ($Message == "decreaseAmmo") {
   $Reply = "{[^Decrease the Start  Ammo][setUnlimitedHealth][selectDecreaseAmmo]}";
} else if ($Message == "selectDecreaseAmmo") {
   $ammo = modNameValue ("ammo",1,1000,-25);  
   $Reply = "{[^Each player will    start with $ammo rounds][decreaseAmmo][decreaseAmmo]}";     
} else if ($Message == "setUnlimitedHealth") {
   $unlimitedHealth = readNameValue ("unlimitedHealth"); 
   if ($unlimitedHealth == "True") {    
     $Reply = "{[^Set Unlimited HealthOff (currently On)][begin][toggleUnlimitedHealth]}";
   } else {
     $Reply = "{[^Set Unlimited HealthOn (currently Off)][setBandages][toggleUnlimitedHealth]}";
   }
} else if ($Message == "toggleUnlimitedHealth") {
   $unlimitedHealth = readNameValue ("unlimitedHealth"); 
   if ($unlimitedHealth == "True") {    
     updateNameValue ("unlimitedHealth", "False");
     $Reply = "{[^Unlimited Health Off][setBandages][setUnlimitedHealth]}";
   } else {
     updateNameValue ("unlimitedHealth", "True");
     $Reply = "{[^Unlimited Health On][begin][setUnlimitedHealth]}";
   }
} else if ($Message == "setBandages") {
   $bandages = readNameValue ("bandages");  
   $Reply = "{[^Increase or decreasenumber of bandages  currently: $bandages][setHealth][increaseBandages]}";
} else if ($Message == "increaseBandages") {
   $Reply = "{[^Increase the number of Bandages][decreaseBandages][selectIncreaseBandages]}";
} else if ($Message == "selectIncreaseBandages") {
   $bandages = modNameValue ("bandages",1,20,1);  
   $Reply = "{[^The game will allow $bandages bandages][increaseBandages][increaseBandages]}";
} else if ($Message == "decreaseBandages") {
   $Reply = "{[^Decrease the number of bandages][setHealth][selectdecreaseBandages]}";
} else if ($Message == "selectdecreaseBandages") {
   $bandages = modNameValue ("bandages",1,20,-1);  
   $Reply = "{[^The game will allow $bandages bandages][decreaseBandages][decreaseBandages]}";   
} else if ($Message == "setHealth") {
   $health = readNameValue ("health");  
   $Reply = "{[^Increase or decrease the starting health currently: $health][begin][increaseHealth]}";
} else if ($Message == "increaseHealth") {
   $Reply = "{[^Increase the Start  Health][decreaseHealth][selectIncreaseHealth]}";
} else if ($Message == "selectIncreaseHealth") {
   $health = modNameValue ("health",1,100,1);  
   $Reply = "{[^Each player will    start with $health          health][increaseHealth][increaseHealth]}";
} else if ($Message == "decreaseHealth") {
   $Reply = "{[^Decrease the Start Health][begin][selectDecreaseHealth]}";
} else if ($Message == "selectDecreaseHealth") {
   $health = modNameValue ("health",1,100,-1); 
   $Reply = "{[^Each player will start with $health hit points][decreaseBandages][decreaseBandages]}"; 
} else {
   $Reply = "{[^Command not understood: $Message][begin][start]}";
}
$cmd = "python sendMessage.py $returnAddress \"$Reply\"";
echo ( "<h1>CMD:</h1><br>$cmd<BR>\n");
exec($cmd);	
     
?>
</body>
</html>