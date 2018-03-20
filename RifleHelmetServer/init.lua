filename = 'ssidPassword.txt'
ip = nil
serverAddress = nil
SSID = nil
password = nil
failureCount = 0
selectString = ""

function trim(s)
  return (s:gsub("^%s*(.-)%s*$", "%1"))
end

function getInfo() 
   handle = file.open (filename, "r")
   if (handle == nil) then
     print ("USE: LOGIN (SSID,PASSWORD),  using default pipboy2000" )
     SSID = "pipboy2000"
     password = ""
   else
     SSID = trim(file.readline())
     password = trim(file.readline())
     print ("[SSID,password]:["..SSID..","..password.."]")
     file.close() 
   end   
end

function getIpAddress ()
   if (ip == nil) then
      print("v1.1, Connecting to AP...["..SSID..","..password.."]")
      wifi.sta.config(SSID, password)      
      ip = wifi.sta.getip()
      if (ip == nil) then 
        failureCount = failureCount + 1
        if (failureCount == 7) then
          file.remove ("ssidPassword.txt") -- delete ssidPassword.txt
          node.restart() -- Wait for user to enter SSID/password
        end
      else
        print ("Got an ip address of : "..ip)
      end 
   else      
      if (serverAddress == nil) then
         print "Waiting until server Address is known"       
      else
         print ("Ready, my address is:"..ip..".") 
         tmr.stop(0)
      end   
   end   
end 

function startListening()
  port = 3333
  srv=net.createServer(net.UDP)
  srv:on("receive", function(connection, pl)
     print("Command Received "..pl)
     
     -- Get server address
     if string.sub (pl,0,6) == "server" then
         print ("Server address received.")       
         serverAddress = string.sub (pl, 8) 
         print ("Address:"..serverAddress.."." )
     end
   end)
  srv:listen(port)   
end 

-- Save SSID and Password
function login (ssid,passwd)
  len = string.len(passwd)
  if ((len< 8) and (len ~= 0))  then 
     print ("\n***ERR****\nPassword length must be 8 characters or more\n")
  else  
     file.open (filename, "w")
     file.writeline (ssid)
     file.writeline (passwd)
     file.close()      
     node.restart()  
  end   
end

function hex_to_char(x)
  return string.char(tonumber(x, 16))
end

function unescape(url)
  return url:gsub("%%(%x%x)", hex_to_char)
end

function handleValue (request)
  _, j = string.find(request, 'SSID=')
  if j ~= nil then
    info = string.sub (request,j+1)
    _, j = string.find (info, '&')
    if j ~= nil then
      ssid = string.sub(info,1,j-1)
      Password = string.sub (info,j+1)
      _, j = string.find( Password, '=')
      if j ~= nil then 
        Password = string.sub (Password, j+1)
        login (unescape(ssid),unescape(Password))
      end
      print ("handleValue ["..ssid..","..Password.."]")
    end  
  end
end
function continueStartup() 
   handle = file.open (filename, "r")
   if (handle == nil) then
      wifi.setmode(wifi.SOFTAP) -- set mode as access point
      MAC = wifi.ap.getmac()
      len = string.len(MAC)
      mac = string.sub (MAC,len-3,len-3)
      mac = mac..string.sub (MAC,len-1,len)
      apSSID = "Device_"..mac
      print ("Login to "..apSSID.." password=123456789, ip=192.168.4.1, and enter SSID, Password")
      wifi.ap.config({ssid= apSSID, pwd="123456789"}) 
      --Note ip address of the web server is always 192.168.4.1
      print(wifi.ap.getip()) -- show the IP address that has been assigned to the device   
      srv=net.createServer(net.TCP)
      srv:listen(80,function(conn)
        conn:on("receive",function(conn,payload)
          print(payload)
          if string.sub(payload,1,4) == "POST" then 
            handleValue (payload)
          end     
          msg = '<!DOCTYPE html>\n'
          msg = msg..'<html lang="en">\n'
          msg = msg..'<head><meta charset="utf-8" />\n'
          msg = msg..'<meta name="viewport" content="width=device-width, initial-scale=1">\n'
          msg = msg..'<title>ESP8266 Dev Kit controller</title></head>\n'
          msg = msg..'<body><h1>Enter your preferred network information</h1>\n'
          msg = msg..'<h2>Note: Password must be at least 8 characters in length (unless open security)</h2>\n'
          msg = msg..'<form method="post">\n' 
          msg = msg..'<Select name="whichAP">'..selectString..'</Select><br>\n'
          msg = msg..'<table border="2px">\n'
          msg = msg..'<tr><th>SSID</th><th>Password</th></tr>\n'
          if SSID ~= nil then 
            msg = msg..'<tr><td><input name="SSID" value="'..SSID..'"></td>\n'
          else
            msg = msg..'<tr><td><input name="SSID"></td>\n'
          end  
          if password ~= nil then 
            msg = msg..'<td><input name="Password" value="'..password..'"></td></tr>\n'       
          else
            msg = msg..'<td><input name="Password"></td></tr>\n'       
          end       
          msg = msg..'</table><BR>\n<input type="submit" value="Submit SSID/Password">\n'
          msg = msg..'</form>\n'
          msg = msg..'</body\n'
          msg = msg..'</html>'
          conn:send(msg)    
        end)
        conn:on("sent",function(conn) conn:close() end)
      end)   
   else
      file.close()
      getInfo()
      wifi.setmode(wifi.STATION) -- set mode as STATION   
      wifi.sta.config(SSID, password)
      MAC = wifi.sta.getmac()
      print ("Logging into the station ["..SSID..","..password.."]" )
      tmr.stop(0)
      tmr.alarm (0,3000,1,getIpAddress) 
      startListening()     
   end
   print ("MAC:"..MAC)
 end

-- print ap list
function listap(t)
      wifi.setmode (wifi.STATION)
      for ssid,v in pairs(t) do
        --authmode, rssi, bssid, channel = 
        --  string.match(v, "(%d),(-?%d+),(%x%x:%x%x:%x%x:%x%x:%x%x:%x%x),(%d+)")
        selectString = selectString.."<option value=\""..ssid.."\">"..ssid.."</option>\n"
      end
      continueStartup()
end
wifi.sta.getap(listap)
  