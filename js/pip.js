   var boyDiv;
   var tabs = [];
   var divs = [];
   var mapDiv;
   
   function stimpakClick() { 
      var request = new XMLHttpRequest();
      var ind;
      var endIndex; 
      var quantity;
      var id;      
           
      request.onreadystatechange = function() {
        if(request.readyState === 4) {
           ind = request.responseText.indexOf ( 'Number of stimpaks:' );
           if (ind > -1) { 
              ind = request.responseText.indexOf ( ':', ind );
              endIndex = request.responseText.indexOf ( ' ', ind);
              if (endIndex > -1) { 
                 quantity = request.responseText.substring ( ind+1, endIndex);
                 alert ( 'Got a quantity of: ' + quantity);
                 id = document.getElementById ("stimpak");
                 id.innerHTML = "STIMPAK (" + quantity + ")";                 
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
                 quantity = request.responseText.substring ( ind+7, endIndex);
                 alert ( 'Got a health quantity of: ' + quantity );                  
                 setHp (quantity);                 
              } else {
                 alert ( 'could not find a space for health' );
              }                             
           } else {
              alert ( 'Could not find \"Health:\" in: ' + request.responseText );            
           }            
           
           // alert ( 'used ajax for (\'useStimpak.php?OwnerId=' + ownerId + '\');' + request.responseText );
        }
      }
      
      if (stimQuantity == 0) {
         alert ( "Out of stimpaks" );
      } else {         
         request.open('Get', 'useStimpak.php?OwnerId=' + ownerId);
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
   
   function setHp (quantity) {
      var hps = document.getElementsByClassName('hp');
      var hp = hps[0];
      alert ( 'setHP to [quantity]: [' + quantity + ']' );
      health = quantity;
      hp.innerHTML = "HP " + health + "/100";      
   } 
   
   function startUp() {      
      getTabs();
      console.log ( 'Found ' + tabs.length + ' tabs' );
      boyDiv = document.getElementById ( "boyDiv" );
      divs.push (document.getElementById ("boyDiv"));
      divs.push (document.getElementById ("mapDiv"));
      divs.push (document.getElementById ("invDiv"));
      divs.push (document.getElementById ("dataDiv"));
      divs.push (document.getElementById ("radioDiv"));
      activate ('stat'); 
      setHp (health);      
   }

document.body.addEventListener("load", startUp(), false);