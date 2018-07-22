<?php
  include "common.inc";
  include "common.php";
  
  $MAC       = getParam ("MAC");
  $MAC       = strtolower($MAC);
  $MAC       = str_replace("-",":",$MAC);
  echo ("MAC replaced: $MAC<br>");
    
  $Username  = getParam ("Username");
  $Typename  = getParam ("Typename");
  echo ("macOwner replaced: $macOwner<br>");
  if ($Typename == "Watch") {
     $pipboyRow = findUsername ($Username);
     if ($pipboyRow) { 
        $sql = "Insert into pipboys (MAC, Username, Typename) values ('$MAC', '$Username', 'Watch')";
        echo ("$sql<br>\n");
        query ($sql);
     } else {
        echo ("Could not find user: $Username<br>\n" );
     }
  } else {    
     $pipboyRow = findUsername ($Username); 
     if ($pipboyRow) { 
        echo ("This name is already taken: $Username.  Please try again <br>\n" );
     } else { 
        $pipboyRow = findPipboy($MAC);
        echo ("Username: $Username<br>\n");
        if ($pipboyRow) {
           echo ( "This MAC address: $MAC is already taken.  Please try again.<br>\n");
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
     }   
  }   
  echo ("<Script>\n");
  // echo ("  window.location.href = 'index.php';\n");
  echo ("</Script>\n");     
?>

<html>
<head> <Title>Add Pipboy</Title>

</head>

<body>

<?php
?>
<input type="button" onclick="window.location.href='index.php';" value="home">
</body>
</html>