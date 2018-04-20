/*
    This sketch sends data via HTTP GET requests to data.sparkfun.com service.

    You need to get streamId and privateKey at data.sparkfun.com and paste them
    below. Or just customize this script to talk to other HTTP servers.

*/

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <DHT.h>
#include "credentials.h"

#define ID 7
#define DHTTYPE DHT11
#define INTERVAL 60000
//#define DEBUG

const char* url = "http://192.168.2.44/logger/Insert.php";

//DHT dht;
DHT dht(2, DHTTYPE);
float temperature, humidity;

void setup() {
	#ifdef DEBUG
    Serial.begin(115200);
    delay(10);
    Serial.println();
    Serial.println();
    Serial.print("Connecting to ");
    Serial.println(ssid);
	#endif

    /*  Explicitly set the ESP8266 to be a WiFi-client, otherwise, it by default,
        would try to act as both a client and an access-point and could cause
        network-issues with your other WiFi-devices on your WiFi-network. */
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, password);

    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
    }

	#ifdef DEBUG
    Serial.println("");
    Serial.println("WiFi connected");
    Serial.println("IP address: ");
    Serial.println(WiFi.localIP());
	#endif

    dht.begin();
}



void loop() {
    temperature = dht.readTemperature();
    humidity = dht.readHumidity();

    if (WiFi.status() == WL_CONNECTED) { //Check WiFi connection status

        HTTPClient http;  //Declare an object of class HTTPClient
        String data = "temp=" + String(temperature) + "&hum=" + String(humidity) + "&id=" + String(ID);
		#ifdef DEBUG
        Serial.println(data);
		#endif
        http.begin(url);  //Specify request destination
        http.addHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
        int httpCode = http.POST(data);         //Send the request

        if (httpCode > 0) { //Check the returning code
            String payload = http.getString();   //Get the request response payload
			#ifdef DEBUG
            Serial.println(payload);             //Print the response payload
			#endif
        }
        http.end();   //Close connection
    }
    
    delay(INTERVAL);
}


