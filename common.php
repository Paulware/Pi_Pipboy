<?php

   function n3 ($value) {
      $val = (int)$value;
      if ($val < 10) { 
         $str = "  $val";
      } else if ($val < 100) {
         $str = " $val";
      } else {
         $str = "$val";
      } 
      return $str;
   }   

   function chooseCommand ($winCmd, $linuxCmd) { // necessary?
       $cmd = $linuxCmd;
       return $cmd;
   }
   
   function getReloads ($OwnerId) {
       $OwnerData = findOwner ($OwnerId);
       $reloads = $OwnerData["Ammo"] / 25;
       return $reloads;              
   } 
   
   function doReload ($OwnerId) {
       $ownerData = findOwner ($OwnerId);
       $ammo = $ownerData["Ammo"];
       if ($ammo > 25) { 
          $ammo = $ammo - 25;
          $sql = "Update pipboys set Ammo=$ammo Where ID=$OwnerId";        
          $q = mysql_query ($sql) or die ("Could not execute: $sql");
       }   
       $rel = $ammo / 25;
       $reloads = (int)$rel;
       return $reloads;
   } 
   
   
   function setState ($OwnerId, $State ) {
       $sql = "Update pipboys set State=$State Where ID=$OwnerId";        
       echo ("$sql<BR>\n" );
       $q = mysql_query ($sql) or die ("Could not execute: $sql");
   }
  
   function useStimpak ($OwnerId) {
      $Owner = findOwner ( $OwnerId);
      
      $Stimpak = findItem ( "Stimpak");
      $ItemId = $Stimpak["ID"];
      $numStimPaks = findInventory ($OwnerId, $ItemId); 
      $quantity = $numStimPaks["Quantity"];     
      echo ("I currently have $quantity stimpaks<br>\n" );
      if ($quantity == 0) { 
         echo ("<h1>Sorry...Out of stimpaks!</h1>\n" );
      } else {                            
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
      }   
   } 
   
   function showStores() {
      // Show the stores
      print ("<hr>\n");
      $result = query ( "Select * From pipboys" );
      $count = 0;
      while ($row = mysql_fetch_assoc ($result)) {		
         $MAC = $row["MAC"];
         $OwnerId = $row["ID"];
         $Username = $row["Username"];      
            
         if (strtolower(substr($MAC,0,5)) == "store" ) { 
            if ($count == 0) {
               echo  ("<H1>Stores<br></h1>\n");      
               echo  ("<table border=\"0px solid\" width=\"90%\">\n" );
               echo  ("<tr bgcolor=\"lightgray\">\n" );
               print ("<th width=\"15%\" align=\"center\">Typename</th>\n");
               print ("<th width=\"15%\" align=\"center\">Name</th>\n");
               print ("<th width=\"15%\" align=\"center\">MAC</th><th width=\"15%\" align=\"middle\">IP Address</th>");
               print ("<th width=\"10%\" align=\"center\">Message</th><th width=\"15%\" align=\"center\">Timestamp</th>");
               print ("<th width=\"10%\">Location</th><th width=\"10%\">Delete</th>");
               print ("<th width=\"10%\">Test Message</th><th>Test Message</th><th>View</th>\n" );
               print ("</tr>\n");
            }  
            $Id = $row["ID"];
            $Typename = $row["Typename"];
            $Username = $row["Username"];
            $Message = $row["Message"];
            $IpAddress = $row["IpAddress"];
            $Location = "[Latitude,Longitude]"; // $row["Nickname"];
            $Timestamp = $row["Timestamp"];      
            print ("<tr>");
            print ("<td align=\"center\">$Typename</td>\n" );
            print ("<td align=\"center\">$Username</td>\n" );
            print ("<td align=\"center\">$MAC</td><td align=\"center\">$IpAddress</td><td align=\"center\">$Message</td>");
            print ("<td align=\"center\">$Timestamp</td><td align=\"center\">$Location</td>" );
            print ("<td align=\"center\"><input type=\"button\" value=\"Delete\" onclick=\"deletePipboy('$MAC');\"></td>");
            print ("<td align=\"center\"><input type=\"button\" value=\"Msg to Server\" onclick=\"sendMessageToServer('$MAC');\"></td>");
            print ("<td align=\"center\"><input type=\"button\" value=\"Msg to Store\" onclick=\"sendMessageToPipboy('$MAC');\"></td>");
            print ("<td align=\"center\"><input type=\"button\" value=\"View Store\" onclick=\"window.location.href='viewOwner.php?OwnerId=$OwnerId';\"></td>");
            print ("</tr>");
            $count = $count + 1;
         }
      }
      if ($count > 0) {
        print ("</table>\n" );
      }      
   }
   
   function consolidate() {
      // For each player
      $sql = "Select * From pipboys";
      $result = query ($sql);
      // Clear all flags first 
      while ($row = mysql_fetch_assoc ($result)) {		
         $OwnerId = $row["ID"]; 
         $sql = "Select * From items";
         $r = query ($sql);        
         // Check each item 
         while ($row = mysql_fetch_assoc ($r)) {		
            $ItemId = $row["ID"];
            $sql = "Select * From inventory Where OwnerId=$OwnerId and ItemId=$ItemId";
            $q = query ($sql); 
            // Get a total for this item  
            $Quantity = 0;
            while ($row = mysql_fetch_assoc ($q)) {		             
              $Quantity = $Quantity + $row["Quantity"];
            }       
            if ($Quantity > 0) {            
              $sql = "Delete From inventory Where OwnerId=$OwnerId and ItemId=$ItemId";
              $d = query ($sql);
              $sql = "Insert into inventory (OwnerId, ItemId, Quantity) Values ($OwnerId, $ItemId, $Quantity)";
              $d = query ($sql);
            }  
         }
      }
      echo ("<br>Consolidation complete<br>\n" );     
   }
  
   function numberOfPlayers ($color) {
      $sql = "Select * From pipboys";
      $result = query ($sql);
      $count = 0;
      while ($row = mysql_fetch_assoc ($result)) {		    
         $Team = $row ["Team"];        
         $Health = $row ["Health"];
         if ($color == $Team) { 
            if ($Health > 0) { 
               $count = $count + 1;
            }   
         }       
      } 
      return $count;
   }   
   
   function spaceRight($msg, $numSpaces) {
      $newValue = $msg;
      $value = $msg;
      $len = strlen ($value);
      if ($len < $numSpaces) {
         while ($len < $numSpaces) { 
            $newValue = "$value ";
            $value = $newValue;
            $len = strlen($value);
         }         
      } 
      //echo ("in spaceRight len of '$newValue': $len<br>\n" );
      //echo ("spaceRight [msg,newValue]: [$msg,$newValue]<br>\n" );
      return $newValue;
   } 
   
   function formatString ($msg, $numSpaces) {
      //echo ("formatString msg before spaceRight: [$msg,$numSpaces]<br>\n" );
      $value = spaceRight ($msg, $numSpaces);
      //echo ("formatString value after spaceRight: [$value,$numSpaces]<br>\n" );
      if (strlen($value) > $numSpaces) { 
         $value = substr ($value, 0, $numSpaces);  
      } 
      $len = strlen ($value);
      echo ("formatString value after substr: [$value,$len]<br>\n" );
      return $value;
   } 

   function getLeaders($offset) {
      $leaders = "User    Hits  Health";
      $result = query ( "Select * From pipboys order by Hits DESC" );
      $count = 0;
      
      if ($offset > 0) {
         $leaders = "";
      }
     
      while ($row = mysql_fetch_assoc ($result)) {		
         $Username = $row["Username"];
         $Hits     = spaceRight ($row["Hits"], 4);
         $Health   = spaceRight ($row["Health"],6); 
         $Typename = $row["Typename"];  
         $Team     = $row["Team"];    
         $name     = formatString ($Username, 8);
         if ($Typename == "Player") {
            if ($Team != ""){                    
               $msg = "$name $Hits $Health";   
               //$msg = formatString ($msg,20);                  
               if ($count >= $offset) { 
                  $leaders = "$leaders$msg";
                  echo ("getLeaders:[$leaders]<br>\n" );
               }   
               $count = $count + 1;
               if ($offset == 0) {
                  if ($count == ($offset + 2)) { 
                     break;
                  }
               } else { 
                  if ($count == ($offset + 3)) { 
                     break;
                  }
               }               
            } else {
               echo ("$Username is not on a team<br>\n" );
            }
         } else { 
            echo ("$Username is a [$Typename]<br>\n" );
         }   
      } 
      echo ("getLeaders got leaders of $leaders<br>\n" );      
      return $leaders;
   } 
   
   function numberOfTeams () {
      $count = 0;
      if (numberOfPlayers ("Green") > 0) { 
         $count = $count + 1;
      } 
      if (numberOfPlayers ("Blue") > 0) {
         $count = $count + 1; 
      }
      if (numberOfPlayers ("Red") > 0) {
         $count = $count + 1;
      } 
      return $count;      
   }   
   
   function findWinningTeam () {
      $winner = "";
      if (numberOfTeams() == 1) {
          $winner = "Red";
          if (numberOfPlayers ("Green" ) > 0) { 
             $winner = "Green";
          } else if (numberOfPlayers ("Blue") > 0) { 
             $winner = "Blue";
          }                     
      } 
      return $winner;
   } 

   function blinkFlags ($team) {
        
      $sql = "Select * From pipboys where Typename='Flag'";
      $result = query ($sql);
            
      while ($row = mysql_fetch_assoc ($result)) {		        
         $Destination = $row["IpAddress"];              
         sendMessage ($Destination, 3333, $team);
         sendMessage ($Destination, 3333, "Winner");         
      }               
   }     
  
   function captureTheFlags() {
     consolidate();
     $count = 0;
     $sql = "Select * From pipboys";
     $result = query ($sql);
     // Clear all flags first 
     while ($row = mysql_fetch_assoc ($result)) {		
        $Name = $row["Username"];
        $MAC = $row["MAC"];
        $ID = $row["ID"];
        $pos = strpos ( $MAC, ":"); 
        $Typename = $row["Typename"];
        if ($Typename == "Flag") { 
           $IpAddress = $row["IpAddress"];
           $Message = "start";
           if ($IpAddress != "") { 
              sendMessage ($IpAddress, 3333, $Message);
           }  
        }   
     }        
     
     $Stimpaks = findItem ("Stimpak");
     $StimpakId = $Stimpaks["ID"];
 
     $sql = "Delete from messages";
     $q = query ($sql);        
     $ammo = getStartAmmo();
     $sql = "Update pipboys Set Ammo=$ammo";
     $q = query ($sql);        
     $sql = "Update pipboys Set Hits=0";
     $q = query ($sql);        
     $sql = "Update pipboys Set Health=25";
     $q = query ($sql);     
     
     $sql = "Select * From pipboys";
     $result = query ($sql);
     $count = 0;
     while ($row = mysql_fetch_assoc ($result)) {		
        $Name = $row["Username"];
        $MAC = $row["MAC"];
        $ID = $row["ID"];
        $pos = strpos ( $MAC, ":"); 
        if ($pos == false) {
           $pos = strpos ( $MAC, "-");
        }
        $Team = $row ["Team"];
        if ($pos == false) { 
          echo ("$Name has no : in MAC address<br>");
        } else if ($Team == "") { 
          echo ("$Name has no team<br>" );
        } else if ($Team == "None") {
          echo ("$Name's team is set to None<br>" );
        } else { 
           $IpAddress = $row["IpAddress"];
           $Message = "start";
           if ($IpAddress != "") {
             sendMessage ($IpAddress, 3333, $Message);
           
             echo ("Giving 4 Stimpaks ID:$StimpakId to the player with ID: $ID<br>\n" );
             modifyInventory ($ID, $StimpakId, 4, 0);           
              
             /*
             // Add 10 bottlecaps for playing the game
             $BottleCaps = findItem ("BottleCaps");
             $BottleCapsId = $BottleCaps["ID"];
             echo ("ID: $ID, Team: [$Team]<br>\n" );
             modifyInventory ($ID, $BottleCapsId, 0, 10);                      
             */
           }  
        }          
     }
     
     // Set Flag colors = None
     print ("Set flag colors = None<br>\n" );
     $sql = "Select * From pipboys Where Typename='Flag'";
     $result = query ($sql);
     while ($row = mysql_fetch_assoc ($result)) {		
        $ID = $row["ID"]; // Primary key
        $sql = "Update pipboys set Team='None' Where ID=$ID";
        $q = query($sql);
     } 
     
     
     $sql = "Delete From systemlog";
     query ($sql);
     $sql = "Insert into systemlog (Message) Values ('Capture the flags started.' )";
     query ($sql);  
     print ("<hr>Done<br>\n" );     
   }
   
   function activePlayers() {      
      $playerNames = "";
      $sql = "Select * From pipboys Where Typename='Player'";
      $result = query ($sql);
      $count = 0;
      while ($row = mysql_fetch_assoc ($result)) {		
         $Name = $row["Username"];
         $MAC = $row["MAC"];
         $ID = $row["ID"];
         $pos = strpos ( $MAC, ":"); 
         $Team = $row ["Team"];
         $IpAddress = $row["IpAddress"];            
         if ($pos === false) {
            echo ("<h1>$Name not active because their MAC does not have a :</h1><br>\n" );
         } else if ($Team == "") {
            echo ("<h1>$Name not active because they are not assigned to a team</h1><br>\n" );
         } else if ($Team == "None") { 
            echo ("<h1>$Name not active because they are assigned to team None</h1><br>\n" );
         } else if ($IpAddress == "") {
            echo ("<h1>$Name not active because they have not called in to the server yet</h1><br>\n" );
         } else { 
            if ($playerNames == "") { 
               $playerNames = $Name;
            } else { 
               $playerNames = "$playerNames $Name";
            } 
         }          
      } 
      echo ("<h2>activePlayers [$playerNames]<br>\n" );            
      return $playerNames;
   }
   
   function getStartAmmo () {
      $ammo = readNameValue ("ammo");     
      $unlimitedAmmo = readNameValue ("unlimitedAmmo"); 
      if ($unlimitedAmmo == "True") {
         $ammo = 9999;
      }    
      return $ammo;
   } 
   
   function getStartHealth () {
      $health = readNameValue ("health");     
      $unlimitedHealth = readNameValue ("unlimitedHealth"); 
      if ($unlimitedHealth == "True") {
         $health = 9999;
      }    
      return $health;
   }
   
   function TDM() {
     consolidate();
     
     $Stimpaks = findItem ("Stimpak");
     $StimpakId = $Stimpaks["ID"];
     $BottleCaps = findItem ("BottleCaps");
     $BottleCapsId = $BottleCaps["ID"];
     $Dragonball = findItem ("DragonBall");
     $DragonballId = $Dragonball["ID"];
     
     $bandages = readNameValue ("bandages");
     
     $sql = "Delete from messages";
     $q = query ($sql);       
     
     $ammo = getStartAmmo();
     $sql = "Update pipboys Set Ammo=$ammo";
     echo ("$sql<br>\n" );
     $q = query ($sql);        
     $sql = "Update pipboys Set Hits=0";
     $q = query ($sql);        
     $health = getStartHealth();
     $sql = "Update pipboys Set Health=$health";
     echo ("$sql<br>\n" );
     $q = query ($sql);     
     
     $sql = "Select * From pipboys";
     $result = query ($sql);
     $count = 0;
     while ($row = mysql_fetch_assoc ($result)) {		
        $Name = $row["Username"];
        $MAC = $row["MAC"];
        $ID = $row["ID"];
        $pos = strpos ( $MAC, ":"); 
        $Team = $row ["Team"];
        if (($pos !== false) && ( $Team != "") && ($Team != "None")){ 
           $IpAddress = $row["IpAddress"];
           $Message = "start";
           
           if ($IpAddress == "") {
             echo ("<h1>Cannot start $Name because they have not reported to server yet</h1><br>\n" );
           } else { 
             sendMessage ($IpAddress, 3333, $Message);
             echo ("Set the number of bandages for each player <br>\n" );           
             modifyInventory ($ID, $StimpakId, (int)$bandages, 0);           
             echo ("ID: $ID, Team: [$Team]<br>\n" );
           }  
        }          
     }   
     
     $sql = "Delete From systemlog";
     query ($sql);
     $sql = "Insert into systemlog (Message) Values ('Capture the flags started.' )";
     query ($sql);  
     print ("<hr>Done<br>\n" );     
   }  
   
   function sendMessage ($ipAddress, $port, $msg ) {
      $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
      $len = strlen($msg);
      socket_sendto($sock, $msg, $len, 0, "$ipAddress", $port);
      socket_close($sock);    
   } 
   
   function killDevices ($MAC) {
      // kill all devices owned by this player
      $sql = "Select * From pipboys where MACOwner='$MAC'";
      $results = query ($sql);
      while ($row = mysql_fetch_assoc ($results)) {
         $ID = $row["ID"];
         $IpAddress = $row["IpAddress"];       
         $Message = "died";
         sendMessage ($IpAddress, 3333, $Message);
         $sql = "Update pipboys set Health=0 Where ID=$ID";         
         $results = query ($sql);
      }    
   }
   
   function findShooter ( $shooter ) {
      echo "Match $shooter MAC address in the database<br>";
      $result = query ( "Select * From pipboys" );
      while ($row = mysql_fetch_assoc ($result))   {		 
         $mac = $row["MAC"];
         list($mac1, $mac2, $mac3, $mac4, $mac5, $mac6) = explode(":", $mac);
         $last3 = substr ( $mac5, 1,1).$mac6;
         if (strtolower($last3) == strtolower($shooter)) {
            echo "Found shooter! $mac<br>\n";
            break;
         }
      } 
      return $row;      
   }
   
   function incrementHits ($shooter, $numHits) {
      $Hits = $shooter["Hits"] + $numHits;
      $shooterId = $shooter["ID"];
      $sql = "Update pipboys set Hits = $Hits Where ID=$shooterId";
      $result = query ($sql);      
   }
   
   function selectAvatar ($avatar) {
      $images = scandir ( '/images' );
      print ($images);
   }
   
   function selectTeam ($team) {  
      if ($team == "Red") {
         echo "<option value=\"Red\" selected>Red</option>\n";
      } else {
         echo "<option value=\"Red\">Red</option>\n";          
      }
      if ($team == "Blue") {
         echo "<option value=\"Blue\" selected>Blue</option>\n";
      } else {
         echo "<option value=\"Blue\">Blue</option>\n";          
      }
      if ($team == "Green") {
         echo "<option value=\"Green\" selected>Green</option>\n";
      } else {
         echo "<option value=\"Green\">Green</option>\n";          
      }
      if (($team != "Blue") && ($team != "Red") && ($team != "Green")) {
         echo "<option value=\"\" selected>None</option>\n";
      } else {
         echo "<option value=\"\">None</option>\n";          
      }
      echo ("</Select>\n");
   }   

   // $Quantity creates a static count
   // $Offset makes a relative count (like adding) 
   function modifyInventory ($OwnerId, $ItemId, $Quantity, $Offset) {
      if ($Offset == 0) { 
         echo ( "modifyInventory set quantity to: $Quantity<br>\n" );
         $sql = "Select * From inventory where OwnerId=$OwnerId and ItemId=$ItemId";
         $result = query ($sql);
         if ($row = mysql_fetch_assoc($result)) {
            $sql = "Update inventory set Quantity=$Quantity Where ItemId=$ItemId and OwnerId=$OwnerId";         
         } else {
            $sql = "Insert into inventory (ItemId, OwnerId, Quantity) values ($ItemId, $OwnerId, $Quantity)";
         }
         echo ("$sql<br>\n" );   
         $q = query ($sql);           
      } else {
         $sql = "Select * From inventory where OwnerId=$OwnerId and ItemId=$ItemId";
         echo ("sql: $sql<br>");
         $result = query ($sql);
         if ($row = mysql_fetch_assoc($result)) {
            $InStock = $row["Quantity"];
            $NewTotal = $InStock + $Offset;
            if ($NewTotal >= 0) { 
              $sql = "Update inventory set Quantity=$NewTotal Where ItemId=$ItemId and OwnerId=$OwnerId";        
              echo ("$sql<br>\n" );   
              $q = query ($sql);           
            } else {
              echo ("ERR cannot have less than 0 of an item currently have: $NewTotal of item: $ItemId<br>" );             
            }            
         } else {
            $sql = "Insert into inventory (ItemId, OwnerId, Quantity) values ($ItemId, $OwnerId, $Offset)";
            echo ("$sql<br>\n" );   
            $q = query ($sql);           
         }       
      }
   }
     
   
   function MACtoIp($mac) {
     $pipboy = findPipboy($mac);
     $IpAddress = $pipboy['IpAddress'];
     return $IpAddress;     
   }
   
   function findOwner ($OwnerId) {
     $sql = "Select * From pipboys Where ID=$OwnerId";
     $result = query ( $sql);
     $value = mysql_fetch_assoc($result);
     return $value;
   }     
   
   function findPipboy($mac) {
     $sql = "Select * From pipboys Where MAC='$mac'";
     $result = query ( $sql);
     $value = mysql_fetch_assoc($result);
     return $value;
   }
   
   function findUsername ($Username) {
     $sql = "Select * From pipboys Where Username='$Username' And Not Typename = 'Watch'";
     $result = query ($sql);
     $value = mysql_fetch_assoc($result);
     return $value;
   }
 
   function findItemByID($ID) {
     $sql = "Select * From items Where ID=$ID";
     $result = query ( $sql);
     $value = mysql_fetch_assoc($result);
     return $value;
   }
   
   function findItem($Name) {
     $sql = "Select * From items Where Name='$Name'";
     $result = query ( $sql);
     $value = mysql_fetch_assoc($result);
     return $value;
   }   
 
   function findInventory ($OwnerId, $ItemId) {
     $sql = "Select * From inventory Where OwnerId=$OwnerId And ItemId=$ItemId";     
     echo ("findInventory, sql: $sql<br>\n" );
     $result = query ( $sql);
     $value = mysql_fetch_assoc($result);
     return $value;
   }
   
   function modNameValue ($name, $minimumValue, $maximumValue, $offset) {
      $currentValue = (int)readNameValue ($name);
      $value = $currentValue + $offset;
      if ($value > $maximumValue) { 
         $value = $maximumValue;
      } else if ($value < $minimumValue) {
         $value = $minimumValue;
      }          
      updateNameValue ($name,"$value");
      $currentValue = (int)readNameValue ($name);
      return $currentValue;
   } 
      
   function readNameValue ($name) {
      $sql = "Select * from namevaluepairs where Name='$name'";
      echo ("$sql<br>\n" );
      $result = query ($sql);
      if ($result) { 
         echo ("sent the $sql<br>\n" );
         $row = mysql_fetch_assoc($result);
         $ID = $row["ID"];
         echo ("Got an ID: $ID<br>\n" );
         $val = $row["Value"];
         echo ("Got a val: $val<br>\n" );
      } else {
         echo ("Could not find a value for $name<br>\n" );
         $val = "unknown";
      }
      return $val;
   }
   
   function updateNameValue ($name, $value) {
     $sql = "Select * from namevaluepairs";
     $result = query ($sql);
     $found = false;
     while ($row = mysql_fetch_assoc ($result))   {		 
        $str = $row["Name"];
        if ($str == $name) { 
           $found = true;
           $sql = "Update namevaluepairs set Value='$value' where Name='$name'";
           break;
        }
     }     
     if (!$found) {
        $sql = "Insert into namevaluepairs (Name,Value) values ('$name', '$value')";
     }
     echo ("Executing sql: $sql<br>");
     $q = mysql_query ($sql) or die ("Could not execute: $sql");
   }
   
   function updateInventoryItem ($PipboyId, $Item, $Quantity) {
     $sql = "Update inventory Set Quantity=$Quantity Where PipboyId=$PipboyId And Item='$Item'";    
     echo ("$sql<BR>\n" );
     $q = mysql_query ($sql) or die ("Could not execute: $sql");
   }
   
   function getRandomItem() {
      $sql = "Select ID from items order by ID DESC";
      $result = query ($sql);
      $row = mysql_fetch_assoc($result);
      $maxID = $row["ID"];
      while (true) {
         $ID = rand (0,$maxID);
         $sql = "Select * From items where ID=$ID";
         $result = query ($sql);
         if ($row = mysql_fetch_assoc($result) ) {
            break;
         }
      }
      return $row;
   }
   
   // Only show real players (not non-player characters)
   function echoPlayers() {
      $result = query ( "Select * From pipboys" );    
      echo ("<option value=0>No One</option>\n" );
      while ($row = mysql_fetch_assoc ($result)) {		
         $Owner = $row["ID"];    
         $Username = $row["Username"];
         $MAC = $row["MAC"];
         $pos = strpos ( $MAC, ":"); 
         if ($pos !== false) {          
            echo (" <option value=\"$Owner\">$Username</option>\n" );
         }      
      }       
   }    
   
   function echoOwnerOptions () {   
      $result = query ( "Select * From pipboys" );    
      echo ("<option value=0>No One</option>\n" );
      while ($row = mysql_fetch_assoc ($result)) {		
         $Owner = $row["ID"];    
         $Username = $row["Username"];
         echo (" <option value=\"$Owner\">$Username</option>\n" );
      }       
   }
   
   function getParam ($name) {
      $value = $_GET["$name"];
      if ($value == "")
        $value = $_REQUEST["$name"];
       return $value;      
   }
   
   /* PHP 7 routines */
   function mysql_fetch_assoc ($result) {
       $row = getResult ($result);
       // echo ("Returning row from mysql_fetch_assoc<br>\n" );
       return $row;
   }    
   
   function mysql_query ( $sql ) {
       try {
           $conn = mysqli_connect ('localhost', 'root', 'pi', 'Paulware', 80);
           //echo "try query '$sql'<br>";
           
           $result = mysqli_query ($conn, $sql);
           if (!$result) {
              echo ("Error message: " );
              echo ("Unknown[" );
              echo ( mysqli_error ($conn) );
              echo ("]<br>" );
           }  
                     
           //echo 'Successfully: '.$sql.'<br>';
           //if ($result) {
           //   echo '<br>Got a return value';
           //}
       } 
       catch (Exception $e) {
          echo 'I had a problem';
          echo 'Error: mysqli_query could not execute: ' . $sql . ' err:' . $e->getMessage();
       }
       return $result;
   }
   
   function query ( $sql ) {
     $q = mysql_query ($sql) or die ("Could not execute: $sql");
     return $q;  
   }    
      
   function getResult ($result) {
     // changing while ($row = mysqli_fetch_assoc($result)) to:
     //          while ($row = $result->fetch_assoc())
     // finally 
     //          while ($row = getResult ($result)) 
     return $result->fetch_assoc();    
   }   
   /* End of PHP7 routines */
   
   function showFlagsTable() {
      $result = query ( "Select * From pipboys where Typename = 'Flag'");
      echo ("<table border = \"1px solid\">\n" );
      // echo ("<tr><th>Flag</th><th>Color</th></tr>\n" );
      $lastTeam = "";
      $winner = 1;
      echo ("<tr>");
      while ($row = mysql_fetch_assoc($result)) {
         $Team = $row ["Team"];
         $Username = $row["Username"];
         if ($Team == "None") { 
           echo ("<td>$Username</td>" );
         } else {
           echo ("<td bgColor=\"$Team\">$Username</td>" );
         }  
         if ($Team == "None") {
           $winner = 0;
         } else if ($lastTeam == "") {
           $lastTeam = $Team;
         } else if ($lastTeam != $Team) { 
           $winner = 0;
         } 
         $lastTeam = $Team;
      }
      echo ("</tr></table><p>\n" );        
   }
   
   function showStimpaks($OwnerId) {
      $Stimpak = findItem ( "Stimpak");
      $StimpakId = $Stimpak ["ID"];
      $Stimpaks = findInventory ( $OwnerId, $StimpakId);
      $Quantity = $Stimpaks["Quantity"];
      if ($Quantity > 0) {
         echo ("<table border = \"1px solid\">\n" );
         echo ("<tr>" );
      
         for ($i=0; $i<$Quantity; $i++) {
            echo ("<td><input type=\"button\" value=\"Stimpak\" onclick=\"window.location.href='useStimpak.php?OwnerId=$OwnerId';\"></td>" );
         }

         echo ("</tr></table><p>\n" );        
      }   
   }
   
   function decrementInventory ($OwnerId, $itemName) {
      $item = findItem ($itemName);
      $itemId = $item["ID"];
      modifyInventory ($OwnerId, $itemId, 0, -1);
      $quantity = getQuantity ($OwnerId, $itemName);
      return $quantity;
   } 
   
   function getQuantity ($OwnerId, $itemName) {
    
      $item = findItem ($itemName);
      $itemId = $item["ID"];
      echo ("getQuantity itemId: $itemId OwnerId:$OwnerId<br>\n" );
      $rowData = findInventory ($OwnerId, $itemId);
      $quantity = $rowData["Quantity"];
      echo ("getQuantity quantity: $quantity<br>\n" );
      return $quantity;
   } 
   
   function showReloads($OwnerId) {
      $Quantity = $getReloads ($OwnerId);
      if ($Quantity > 0) {
         echo ("<table border = \"1px solid\">\n" );
         echo ("<tr>" );
      
         for ($i=0; $i<$Quantity; $i++) {
            echo ("<td><input type=\"button\" value=\"Reload\" onclick=\"window.location.href='useReload.php?OwnerId=$OwnerId';\"></td>" );
         }
         echo ("</tr></table><p>\n" );        
      }   
   }
   
   
   // Get lines ready for display
   function unescapeCharacters ($line) {
     $line = str_replace ('&#060;','<',$line);
     $line = str_replace ('&#062;','>',$line);
     $line = str_replace ('&#146;','\'',$line);
     $line = str_replace ('&#147;','"',$line);
	    $line = str_replace ('\r\n','<BR>',$line);
     //$line = str_replace ('&','$2$1$', $line);	 
	    $line = str_replace ('&#092;','\\',$line);
	    return $line;
   }
   
   function escapeCharacters ( $line ) {
     $line = str_replace ('<','&#060;',$line);
     $line = str_replace ('>','&#062;',$line);
     $line = str_replace ('\'','&#146;',$line);
     $line = str_replace ('"','&#147;',$line);
	    $line = str_replace ('\\','&#092;',$line);
     //$line = str_replace ('\r\n', '<BR>', $line );
     // Do not escape <BR>, <LI>, <UL>, or <OL> or others
     $line = str_replace ('&#060;BR&#062;','<BR>',$line );
     $line = str_replace ('&#060;LI&#062;','<LI>',$line );
     $line = str_replace ('&#060;UL&#062;','<UL>',$line );
     $line = str_replace ('&#060;OL&#062;','<OL>',$line );
     $line = str_replace ('&#060;/LI&#062;','</LI>',$line );
     $line = str_replace ('&#060;/UL&#062;','</UL>',$line );
     $line = str_replace ('&#060;/OL&#062;','</OL>',$line );
     $line = str_replace ('&#060;br&#062;','<br>',$line );
     $line = str_replace ('&#060;li&#062;','<li>',$line );
     $line = str_replace ('&#060;ul&#062;','<ul>',$line );
     $line = str_replace ('&#060;ol&#062;','<ol>',$line );
     $line = str_replace ('&#060;/li&#062;','</li>',$line );
     $line = str_replace ('&#060;/ul&#062;','</ul>',$line );
     $line = str_replace ('&#060;/ol&#062;','</ol>',$line );
     $line = str_replace ('&#060;b&#062;','<b>',$line );
     $line = str_replace ('&#060;/b&#062;','</b>',$line );
     $line = str_replace ('&#060;B&#062;','<B>',$line );
     $line = str_replace ('&#060;/B&#062;','</B>',$line );
	    $line = str_replace ('&#092;','\\',$line );

     return $line;
   }

?>