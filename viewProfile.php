<html>
<head> <Title>View Owner's Profile</Title>
<?php
  include "common.inc";
  include "common.php";
  $OwnerId = getParam ("OwnerId" );
  $Owner = findOwner ( $OwnerId);
  $Username =   $Owner ["Username"];
  $Health = $Owner ["Health"];
  $MAC = $Owner["MAC"];
  $Hits = $Owner["Hits"];
  //$Ammo = $Owner["Ammo"];
  $MACOwner = $Owner["MACOwner"];
  $Typename = $Owner["Typename"];
  $Team = $Owner["Team"];
  $Avatar = $Owner["Avatar"];
  $Weapon = $Owner["Weapon"];
?>
<meta charset="utf-8" />'
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
html {
  background: url("images/pipboyBackground.jpg") no-repeat fixed;
  background-size: cover;
}
body {
  color: white;
}
</style>

</head>
<script>
  <?php
  echo "var ID=$OwnerId;\n";
  ?>
  function changeAvatar (Avatar) { 
    var sql = "Update pipboys Set Avatar='" + Avatar + "' Where ID=" + ID;
    window.location.href='query.php?sql=' + escape(sql);     
  }
  
  function changeWeapon (Weapon) {
    var sql = "Update pipboys Set Weapon='" + Weapon + "' Where ID=" + ID;
    window.location.href='query.php?sql=' + escape(sql);     
  }
  function saveName () {
    var Username = document.all.Username.value;
    var sql = "Update pipboys set Username='" + Username + "' Where ID=" + ID;
    window.location.href='query.php?sql=' + escape(sql);
  }
  function selectTeam (team) { 
    var sql = "Update pipboys set Team='" + team + "' Where ID=" + ID;
    window.location.href='query.php?sql=' + escape(sql);
  }
</script>
<body>
<?php
  // Show Hits and Health
  echo ("<H2><Font color=\"$Team\">$Username</Font></H2>\n");
  echo ("<Table border=\"2px\">\n" );
  echo ("<tr><th>Name</th><th>Value</th><th>Description</th></tr>\n" );
  echo ("<tr><td>Avatar</td><td>" );
  echo ("<Select onchange=\"changeAvatar(this.value);\">" );
  $dir = 'images/avatars';
  $images = scandir ($dir);
  echo "<option value=\"None\">None</option>\n";
  foreach ($images as $key => $value ) { 
    if (!in_array ($value, array ('.', '..' ) ) ) { 
      if ($Avatar == $value) { 
        echo "<option value=\"$value\" selected>$value</option>\n";          
      } else { 
        echo "<option value=\"$value\">$value</option>\n";          
      }      
    }
  }  
  echo ("</Select></td><td>Player's Icon<img src=\"images/avatars/$Avatar\" width=100></td></tr><br>\n" );
  echo ("<tr><td>Primary Weapon</td><td><Select onchange=\"changeWeapon(this.value);\"><option>None</option>" );
  // Show all weapons in this players inventory
  $result = query ( "Select * From inventory Where OwnerId=$OwnerId" );
  $count = 0;
  $Description = "&nbsp;";
  while ($row = mysql_fetch_assoc ($result)) {		
     $ID = $row["ID"];
     $ItemId = $row["ItemId"];
     $Item = findItemByID ($ItemId);
     $ItemName = $Item["Name"];
     $Quantity = $row["Quantity"];
     $ForSale = $row["ForSale"];
     $Price = $row["Price"];
     $InVault = $row["InVault"];
     if ($ItemName == $Weapon) { 
       echo ("<option selected>$ItemName</option>\n" );
       $Description = $Item["Description"];
     } else {  
       echo ("<option>$ItemName</option>\n" );
     }
  }      
  
  echo ("</Select></td><td>$Description</td></tr>\n" );
  echo ("<tr><td>User Name </td><td><input name=\"Username\" value=\"$Username\"><input type=\"button\" value=\"save\" onclick=\"saveName();\"></td><td>Name of Player</td></tr>\n" );
  echo ("<tr><td>Team Color</td>" );
  echo ("<td><Select onchange=\"selectTeam(this.value)\";>" );
  selectTeam ($Team);
  echo ("</Select></td><td>Which team you have joined</td></tr>\n" );
  echo ("<tr><td>Experience Points</td><td>?</td><td>Used to move up a level</td></tr>\n" );
  echo ("<tr><td>Level</td><td>?</td><td>Used to move up a tier</td></tr>\n" );
  echo ("<tr><td>Tier</td><td>?</td><td>Used to unlock special abilities</td></tr>\n" );
?>
</Table>
<br>
<?php
   echo ("<input type=\"button\" value=\"back\" onclick=\"window.location.href='viewOwner.php?OwnerId=$OwnerId';\">" );
?>      
</body>
</html>