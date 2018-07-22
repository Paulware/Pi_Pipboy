#!/usr/bin/python
from socket import *
import re
import os
import time
import subprocess
import os

subprocess.call(["/usr/bin/php", "teamDeathMatch.php"])

def getLocalAddress ():
  ipAddress = '192.168.0.X'
  line = os.popen("/sbin/ifconfig wlan0").read().strip()  
  p = re.findall ( r'[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+', line )
  if p: 
     ipAddress = p[0]   
     
  return ipAddress 
  
def getBroadcastAddress ():
  address = getLocalAddress()
  index = address.rfind ('.')
  addr = address[0:index] + '.255'
  return addr 

def broadcastMessage(msg): 
   try: 
      port = 3333
      sock = socket(AF_INET, SOCK_DGRAM)
      sock.bind (('',0)) # bind to any old port 
      sock.setsockopt (SOL_SOCKET, SO_BROADCAST, 1)
      destination = getBroadcastAddress() # '192.168.0.255'  
      sock.sendto(msg, (destination, port)) # broadcast to all devices listening on port 3333
      print 'Sent ' + msg + ' to ' + destination + ':' + str(port) + '\n'
            
   except Exception as inst:
      print ('Could not send ' + msg + ' to all players' )
      print str(inst)  
  
startTime = time.time()
printTime = time.time()
countdownTime = time.time()
broadcastMessage ('server ' + getLocalAddress())
time.sleep (3) 
broadcastMessage ('start')
print ("Starting 10 minute game" )
elapsedTime = 0
while (elapsedTime < 600): 
    elapsedTime = time.time() - startTime
    if ((time.time() - countdownTime) > 10): # Display seconds left
       countdownTime = time.time()
       print ( str(600 - elapsedTime) + ' seconds left' ) 

    if ((time.time() - printTime) > 30): # Reload
       # call php to reload all active players
       printTime = time.time()
       broadcastMessage ('reload')