#include <SoftwareSerial.h>
#include <MatchStrings.h>
#include "IR.h"
#include "IRPipboy.h"
#include "TimerOne.h"
#include <TriColorLED.h>
#include <EEPROM.h>

#define ESPRXPIN 11
#define ESPTXPIN 10

#define IRTRANSMITPIN 3
#define IRRECEIVEPIN 17

#define TRIGGERPIN 16

#define FREEZETIME 2000
#define PIEZOPIN 2

IRPipboy ir (IRRECEIVEPIN, IRTRANSMITPIN, &Timer1);

TriColorLED led = TriColorLED(5,4,6); //Green, Blue, Red
boolean initialized = false;
boolean tryingToConnect = true;
boolean serverUnknown = true;
boolean died = false;
boolean checksumBad = true;

// Timers
unsigned long fireTimeout = 0;
unsigned long redTimeout = 0;
unsigned long greenTimeout = 0;
unsigned long blueTimeout = 0;
unsigned long connectTimeout = 0;
unsigned long serverTimeout = 0;
unsigned long toneTimeout = 0;

int ammo = 25;

SoftwareSerial espSerial (ESPRXPIN, ESPTXPIN);

MatchStrings matchStrings;

void killMyself() {
  if (!died) {
     tone(PIEZOPIN, 2500);          
     toneTimeout = millis() + 1500;
  }
  died = true;
  Serial.println ( "I am died" );
  EEPROM.write(4,0); // Writing dead  
}

void setup() {
  int matched=0; // return value not used
  int checkSum=0;
  int hexValue;  
  char ch;
  // put your setup code here, to run once:
  Serial.begin (9600);
  Serial.println ( "Ready" );
  matchStrings = MatchStrings();
  
  espSerial.begin (9600);
  while (espSerial.available() ) {
    ch = espSerial.read(); // Flush the rx buffer
  }

  ir.init(); 
  Timer1.initialize (25);
  Timer1.attachInterrupt(callback,25);  

  led.setColor (BLUE);
  
  ammo = EEPROM.read(5);
  if ((ammo > 25) || (ammo < 0)) { 
    ammo = 25;
  }

  if (EEPROM.read(4) == 0) {
    Serial.println ( F("I am a dead soldier") );
    killMyself();
  }
  led.setColor (BLUE);  
  Serial.print ( F("ammo: ") );
  Serial.println ( ammo );

  matchStrings.addString ("MAC:");
  matchStrings.addString ("Connected...");
  matchStrings.addString ("died");
  matchStrings.addString ("start");
  matchStrings.addString ("Trying to connect...");
  matchStrings.addString ("Server unknown");
  matchStrings.addString ("sent Hello");
  matchStrings.addString ("hit");
  matchStrings.addString ("reload");  
  matchStrings.addString ("CTS");
  matchStrings.addString ("Green");
  matchStrings.addString ("Blue");
  matchStrings.addString ("Red");   
  
  for (int i=0; i<4; i++) {
    ir.fourNibbles[i] = EEPROM.read(i);
    Serial.print ( "Read: " );
    Serial.println ( ir.fourNibbles[i],HEX );
    checkSum = checkSum ^ ir.fourNibbles[i];
    if (ir.fourNibbles[i] != 255) {
      checksumBad = false;
    }
    if (!checksumBad) {
       ir.createFirePulse (ir.fourNibbles[0], ir.fourNibbles[1], ir.fourNibbles[2],
                           ir.fourNibbles[3]);
    } else {
       Serial.println ( "Checksum was bad, call showMAC()" );
    }
  }   

  pinMode (TRIGGERPIN,INPUT);
  digitalWrite (TRIGGERPIN,1); // pullup
  espSerial.println (" "); // Restart network device
}

void callback() // Timer1 is set to 25 microsecond to balance PWM output
{
  static bool skip = false;
  skip = !skip;
  if (!skip)
    ir.callback();
}

int chToHex (char ch) {
  int value;
  if ((ch >= '0') && (ch <= '9'))
     value = ch - '0';
  else
     value = ch - 'a' + 10;
  return value;
}

char intToHex (int hex ) {
  char ch;
  if ((hex >=0) && (hex <= 9)) {
    ch = hex + '0';
  } else {
    ch = hex + 'a' - 10;
  }
  return ch;
}

void showNibbles() {
  Serial.print ( "fourNibbles[] = " );
  for (int i=0; i<4; i++) { 
     if (i==0) {      
       Serial.print ( "{0x" );
       Serial.print ( ir.fourNibbles [i],HEX );
     } else if (i == 3) { 
       Serial.print ( ",0x" );  
       Serial.print ( ir.fourNibbles [i],HEX );
       Serial.println ( "};" );
     } else { 
       Serial.print ( ",0x" );
       Serial.print ( ir.fourNibbles [i],HEX );
     }
  } 
}

void fireShot() {
  if (!initialized) { 
     Serial.println ( "Cannot fire because haven't logged on to network" );
     led.setColor (RED);
     redTimeout = millis() + 1000;
  }
  else if (fireTimeout) {
    Serial.println ( "Wait for previous shot to finish" );
  } else {
    if (died) {
      Serial.println ( "You have died" );
    } else {
      if (checksumBad) {
        Serial.println ( "No checksum" );
      } else if (ammo == 0) {
        Serial.println ( "Out of ammo (reload)" );      
        tone(PIEZOPIN, 1000);          
        toneTimeout = millis() + 5000;
        led.setColor (RED);
        redTimeout = millis() + 5000;
      } else {
        if (led.currentColor () == "GREEN") {
           led.setColor (BLUE);
           blueTimeout = millis() + 300;
        } else if (led.currentColor() == "RED") {
           led.setColor (GREEN);
           greenTimeout = millis() + 300;
        } else if (led.currentColor() == "BLUE") {
           led.setColor (GREEN);
           greenTimeout = millis() + 300;
        }
        Serial.println ( F("Firing...") );
        ammo = ammo - 1;
        EEPROM.write (5,ammo);
        ir.fireData();
        fireTimeout = millis() + 300;
        Serial.print ( F("Shot, Ammunition:" ));
        Serial.println ( ammo );
        //tone(PIEZOPIN, 1000);          
      }  
    }         
  }
  delay (300);
}

void updateLed() {
   static long updateTimeout = 0; 
   if (blueTimeout) { 
      if (millis() > blueTimeout) { 
        blueTimeout = 0;      
      }
   }
  
   if (redTimeout) { 
      if (millis() > redTimeout) { 
         redTimeout = 0;
      }
   }
  
   if (greenTimeout) { 
      if (millis() > greenTimeout) {
         greenTimeout = 0;
      }
   }
  
   if (!greenTimeout && !redTimeout && !blueTimeout) {
      if (millis() > updateTimeout) { 
         updateTimeout = millis() + 100;
         if (died) {
            led.setColor (RED);
         } 
         else if (initialized) {
            led.setColor (GREEN);
         }
         else // !initialized 
         {
            led.setColor (BLUE);
         }
      }   
   }
}

void initialize() {
  // Let the user know they are connected to the server
  if (!initialized) { 
     tone(PIEZOPIN, 2500);          
     toneTimeout = millis() + 1500;
  }
  initialized = true;
}

void loop() {
  unsigned int irValue; 
  int matched=-1; 
  static boolean gettingMAC = false;
  unsigned int hexValue;  
  static char MAC[] = "   ";
  static int redBlue = 0;
  static int redGreen = 0;
  static int lastTrigger = 1;
  char ch;      
  int value = digitalRead (TRIGGERPIN); // trigger


  if (lastTrigger != value) {
     if (value == 0) { 
        fireShot();
     }
     lastTrigger = value;
  }

  if (Serial.available() ) {
    ch = Serial.read();
    if (ch == '!') { 
      fireShot();
    } else {
      espSerial.print (ch);
    }  
  }

  if (espSerial.available() ) {
    ch = espSerial.read();
    Serial.print (ch);
     
    matched = matchStrings.findMatch (ch);
    switch (matched) {
        case -1: // no match 
           break; 
           
        case 0: 
          Serial.println ( F ("MAC Detected with match strings" ));
          gettingMAC = true;
        break;

        case 9: // CTS
        case 6: // sent Hello
        case 1: // Connected...
            initialize();
            if (checksumBad) { 
              espSerial.println ( "showMAC()" ); // Request mac address
            }  
            serverTimeout = 0;
            tryingToConnect = false;
            serverUnknown = false;
        break;

        case 2: // died
          killMyself();
        break;

        case 3: // start
          Serial.println ( "I am rebornded" );
          died = false;
          EEPROM.write (4,1);
          ammo = 25;
          EEPROM.write (5,25);
          initialize();
        break;

        // Let the user know we have network issues
        case 4: // Trying to connect...
           tryingToConnect = true;
        break;

        // Raspberry pi server has no broadcast its address yet
        case 5: // Server unknown
           tryingToConnect = false;
           serverTimeout = millis() + 100;
        break;

        case 7: // hit
          initialize();
          led.setColor (RED);
          redTimeout = millis() + 1000;
          tone(PIEZOPIN, 3000);          
          delay (300);
          tone(PIEZOPIN, 2000);          
          delay (300);
          tone(PIEZOPIN, 1000);          
          delay (300);
          tone(PIEZOPIN, 500);
          toneTimeout = millis() + 800;
        break;

        case 8: // Got a reload command
          ammo = 25;
          EEPROM.write (5,25);
        break;

        case 10: // Player assigned to Green team
          led.setColor (GREEN);
          greenTimeout = millis() + 1000;
          break;

        case 11: // Player assigned to Blue team
          led.setColor (BLUE);
          blueTimeout = millis() + 1000;
          break;

        case 12: // Player assigned to Red team
          led.setColor (RED); 
          redTimeout = millis() + 1000;
          break;
          
        default:
        break;
    } 

   if (ch == 10) {
   } else if (ch == 13) { 
        if (gettingMAC)  { 
           Serial.print ( F("\nConvert this MAC to a hex digit:") );
           Serial.println ( MAC );
           gettingMAC = false;
           hexValue = (chToHex(MAC[0]) * 0x100) + (chToHex(MAC[1]) * 0x10) + chToHex(MAC[2]); 
           Serial.print ( F("Got a hex digit: ") );
           Serial.println ( hexValue, HEX );
           ir.createFireSequence (hexValue);
           Serial.println ( F("Done creating a fire sequence") );
           showNibbles();
           checksumBad = false;
           for (int i=0; i<4; i++) {
              EEPROM.write(i, ir.fourNibbles[i]);             
           }
        }  
   } else if (ch != ':' ) { 
       MAC [0] = MAC [1];
       MAC [1] = MAC [2];
       MAC [2] = ch;
   }              
 }

 irValue = ir.IRDetected(); 
 if (irValue != 0) { 
   Serial.print ( "irValue: " );
   Serial.println ( irValue, HEX );
   espSerial.print ( "sendMsg(\"ouch:" );
   espSerial.print ( intToHex(( irValue & 0xF00) / 0x100) );
   espSerial.print ( intToHex(( irValue & 0xF0) / 0x10));
   espSerial.print ( intToHex(irValue & 0xF));
   espSerial.println ( "\")" );
   led.setColor (BLUE);
   blueTimeout = millis()+ FREEZETIME; 
 }   

 // Check if I am firing the weapon.
 if (fireTimeout) {
   if (millis() > fireTimeout) {   
      fireTimeout = 0;
      Serial.println ( F("Fire Timeout Done, You can now fire again") );
   }
 }

 if (toneTimeout) {
   if (millis() > toneTimeout) {
     noTone (PIEZOPIN);
     toneTimeout = 0;
   }
 }

 updateLed();
 led.update();

}
