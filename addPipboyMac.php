<?php
  include "common.inc";
  include "common.php";
  
  $MAC       = getParam ("MAC");
  $Username  = getParam ("Username");
  $Typename  = getParam ("Typename");
  $pipboyRow = findPipboy($MAC);
?>

<html>
<head> <Title>Add Pipboy</Title>

</head>

<body>

<?php
  echo ("Username: $Username<br>\n");
  if ($pipboyRow) {
     echo ( "Error...That pipboy is already assigned to a user.");
  } else {
     if (strlen ($MAC) > 17) {
        $MAC = substr ( $MAC, 0, 17);
     }
     echo ("MAC address[$MAC]<br>\n");
     $sql = "Insert into pipboys (MAC, Username, Typename) values ('$MAC', '$Username', '$Typename')";
     echo ("$sql<br>\n");
     query ($sql);
     echo ( "Added $Username to the pipboys table");  
  }
  echo ("<Script>\n");
  // echo ("  window.location.href = 'index.php';\n");
  echo ("</Script>\n");     
?>
<input type="button" onclick="window.location.href='index.php';" value="home">
</body>
</html>