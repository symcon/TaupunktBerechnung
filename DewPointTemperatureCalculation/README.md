# TaupunktBerechnung
Berechnung des Taupunktes mit der relativen Luftfeuchtigkeit und Raumtemperatur. 

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Berechnung der Taupunkttemperatur
* Berechnung der Temperatur ab dem das Risko zum Schimmelbefall steigt

### 2. Voraussetzungen

- IP-Symcon ab Version 6.4

### 3. Software-Installation

* Über den Module Store das 'TaupunktBerechnung'-Modul installieren.

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'TaupunktBerechnung'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
 Raumtemperatur  | Temperatur, welche im Raum herrscht in °C
Luftfeuchtigkeit | Relative Luftfeuchtigkeit, welche herrscht 

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name                          | Typ     | Beschreibung
----------------------------- | ------- | ------------
Taupunkt                      | float   | Temperatur, bei unterschreiten wird überschüssiger Wasserdampf sich in Wasser wandeln 
Temperatur für Schimmelgefahr | float   | Temperatur, bei unterschreiten dieser steigt das Risiko des Schimmelbefalls
Alarm für Schimmelgefahr      | boolean | Alarm, welcher auslöst wenn die Temperatur für Schimmelgefahr unterschritten wird. Ausgeschaltet wird dieser, wenn die Raumtemperatur 1°C über der Temperatur für Schimmelgefahr ist.