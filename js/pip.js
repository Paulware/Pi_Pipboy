   var boyDiv;
   var tabs = [];
   var divs = [];
   var mapDiv;
   
   function stimpakClick() { 
      var request = new XMLHttpRequest();
      var ind;
      var endIndex; 
      var hpQuantity;
      var id;      
           
      if (stimQuantity == 0) {
         alert ( "Out of stimpaks" );
      } else {    
         request.onreadystatechange = function() {
           if(request.readyState === 4) {
              ind = request.responseText.indexOf ( 'Number of stimpaks:' );
              if (ind > -1) { 
                 ind = request.responseText.indexOf ( ':', ind );
                 endIndex = request.responseText.indexOf ( ' ', ind);
                 if (endIndex > -1) { 
                    stimQuantity = request.responseText.substring ( ind+1, endIndex);
                    id = document.getElementById ("stimpak");
                    id.innerHTML = "STIMPAK (" + stimQuantity + ")";                 
                 } else {
                    alert ( 'could not find a space' );
                 }                             
              } else {
                 alert ( 'Could not find number of stimpaks' );
              } 
                         
              ind = request.responseText.indexOf ( 'Health:' );
              if (ind > -1) { 
                 endIndex = request.responseText.indexOf ( ' ', ind);
                 if (endIndex > -1) { 
                    hpQuantity = request.responseText.substring ( ind+7, endIndex);
                    //alert ( 'Got a health quantity of: ' + quantity );                  
                    setHp (hpQuantity);                 
                 } else {
                    alert ( 'could not find a space for health' );
                 }                             
              } else {
                 alert ( 'Could not find \"Health:\" in: ' + request.responseText );            
              }            
              
              // alert ( 'used ajax for (\'useStimpak.php?OwnerId=' + ownerId + '\');' + request.responseText );
           }
         }
      
         request.open('Get', 'useStimpak.php?OwnerId=' + ownerId);
         request.send();
      }   
   } 
   
   function reloadClick() { 
      var request = new XMLHttpRequest();
      var ind;
      var endIndex; 
      var ammoQuantity;
      var id;      
           
      if (reloadQuantity == 0) {
         alert ( "Out of reloads" );
      } else {    
         request.onreadystatechange = function() {
           if(request.readyState === 4) {
              ind = request.responseText.indexOf ( 'Number of Reloads:' );
              if (ind > -1) { 
                 ind = request.responseText.indexOf ( ':', ind );
                 endIndex = request.responseText.indexOf ( ' ', ind);
                 if (endIndex > -1) { 
                    reloadQuantity = request.responseText.substring ( ind+1, endIndex);
                    id = document.getElementById ("reload");
                    id.innerHTML = "Reloads(" + reloadQuantity + ")";                 
                 } else {
                    alert ( 'could not find a space' );
                 }                             
              } else {
                 alert ( 'Could not find number of reloads in:' + request.responseText );
              } 
                              
              ind = request.responseText.indexOf ( 'Ammo:' );
              if (ind > -1) { 
                 endIndex = request.responseText.indexOf ( ' ', ind);
                 if (endIndex > -1) { 
                    ammoQuantity = request.responseText.substring ( ind+5, endIndex);
                    // alert ( 'Got a ammo quantity of: ' + ammoQuantity );
                    setAmmo (ammoQuantity);                 
                 } else {
                    alert ( 'could not find a space for ammo' );
                 }                             
              } else {
                 alert ( 'Could not find \"Ammo:\" in: ' + request.responseText );            
              }            
              
              // alert ( 'used ajax for (\'useReload.php?OwnerId=' + ownerId + '\');' + request.responseText );
           }
         }
      
         request.open('Get', 'useReload.php?OwnerId=' + ownerId);
         request.send();
      }   
   } 
   
   function activate(className) {    
      for (i=0; i<tabs.length; i++) {
        tabs[i].className = tabs[i].originalName;
      }
     
      for (i=0; i<divs.length;i++) {
        divs[i].style.visibility ='hidden'; // Hide all divs
      }    
     
      if (className == 'stat') {
         el = document.getElementById ( 'statIcons');
         el.style.visibility = 'visible';
         boyDiv.style.visibility = 'visible';
      } else { 
         el = document.getElementById ( 'statIcons');
         el.style.visibility = 'hidden';
         if (className == 'map') {
             document.getElementById ('mapDiv').style.visibility='visible';   
         } else if (className == 'inv') {
             document.getElementById ('invDiv').style.visibility='visible';
         } else if (className == 'data') {
             document.getElementById ('dataDiv').style.visibility='visible';           
         } else if (className == 'radio') {
             document.getElementById('radioDiv').style.visibility='visible';
         }
      }
      
      // Change appearance of tab
      var x = document.getElementsByClassName(className);
      for (i = 0; i < x.length; i++) {
          x[i].className += " active";          
      }      
   }

   function getTabs() {
      var parent = document.getElementsByClassName ("holder")[0];     
      var child;
      for (var i=0; i < parent.childNodes.length; i++) {
         child = parent.childNodes[i];
         if ((child.className == "stat") ||
             (child.className == "inv") ||
             (child.className == "data") || 
             (child.className == "map" ) ||
             (child.className == "radio") ) {
             child.originalName = child.className;
             tabs.push (child);           
         }
      }       
   }
   
   function setAmmo (quantity) { 
      var ammo = document.getElementsByClassName('ap');
      var ap = ammo[0];
      // alert ( 'setAmmo to [quantity]: [' + quantity + ']' );
      ammunition = quantity;
      ap.innerHTML = "AP " + ammunition + "/125";      
   } 
   
   function setHp (quantity) {
      var hps = document.getElementsByClassName('hp');
      var hp = hps[0];
      var msg = "HP "; 
      if (quantity < 10) { 
        msg = msg + "0"; 
      } 
      
      //alert ( 'setHP to [quantity]: [' + quantity + ']' );
      health = quantity;
     
      hp.innerHTML = msg + health;      
   } 
   
   function startUp() {      
      getTabs();
      //console.log ( 'Found ' + tabs.length + ' tabs' );
      boyDiv = document.getElementById ( "boyDiv" );
      divs.push (document.getElementById ("boyDiv"));
      divs.push (document.getElementById ("mapDiv"));
      divs.push (document.getElementById ("invDiv"));
      divs.push (document.getElementById ("dataDiv"));
      divs.push (document.getElementById ("radioDiv"));
      activate ('stat'); 
      //alert ( 'set health to: ' + health );
      setHp (health); 
       
      setAmmo (ammunition);      
   }

document.body.addEventListener("load", startUp(), false);