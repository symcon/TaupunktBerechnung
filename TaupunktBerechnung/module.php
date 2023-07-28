<?php

declare(strict_types=1);
    class TaupunktBerechnung extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyInteger('Humidity', 0);
            $this->RegisterPropertyInteger('RoomTemperature', 0);

            $this->RegisterVariableFloat('DewPoint', $this->Translate('Dew Point'), '~Temperature', 0);
            $this->RegisterVariableFloat('MoldRisk', $this->Translate('Risk of Mold Temperature'), '~Temperature', 1);
            $this->RegisterVariableBoolean('MoldAlert', $this->Translate('Alert Risk of Mold'), '~Alert', 2);
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();

            $humidityID = $this->ReadPropertyInteger('Humidity');
            $temperatureID = $this->ReadPropertyInteger('RoomTemperature');

            if (!IPS_VariableExists($humidityID) || !IPS_VariableExists($temperatureID)) {
                $this->SetStatus(200); //One of the Variable is missing
                return;
            } else {
                $this->SetStatus(102); //All right
            }

            //Messages
            //Unregister all messages
            foreach ($this->GetMessageList() as $senderID => $messages) {
                foreach ($messages as $message) {
                    $this->UnregisterMessage($senderID, $message);
                }
            }
            //Register necessary messages
            $this->RegisterMessage($humidityID, VM_UPDATE);
            $this->RegisterMessage($temperatureID, VM_UPDATE);

            //Initial calculation
            $this->Calculate();
        }

        public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
        {
            $this->SendDebug('Sender ' . $SenderID, 'Message ' . $Message, 0);
            if ($Message === VM_UPDATE) {
                $this->Calculate();
            }
        }

        private function Calculate(): bool
        {
            $humidityID = $this->ReadPropertyInteger('Humidity');
            $temperatureID = $this->ReadPropertyInteger('RoomTemperature');

            if (!IPS_VariableExists($humidityID) || !IPS_VariableExists($temperatureID)) {
                return false;
            }

            $humidity = GetValue($humidityID);
            $temperature = GetValue($temperatureID);

            $this->SetValue('DewPoint', $this->CalculateDewPoint($humidity, $temperature));
            $this->SetValue('MoldRisk', $this->CalculateMoldRisk($humidity, $temperature));

            return true;
        }

        private function CalculateDewPoint(float $humidity, float $temperature): float
        {
            //Source https://www.wetterochs.de/wetter/feuchte.html

            /*Bezeichnungen:
                r = relative Luftfeuchte
                T = Temperatur in °C
                TK = Temperatur in Kelvin (TK = T + 273.15)
                TD = Taupunkttemperatur in °C
                DD = Dampfdruck in hPa
                SDD = Sättigungsdampfdruck in hPa

                Parameter:
                a = 7.5, b = 237.3 für T >= 0
                a = 7.6, b = 240.7 für T < 0 über Wasser (Taupunkt)
                a = 9.5, b = 265.5 für T < 0 über Eis (Frostpunkt)

                R* = 8314.3 J/(kmol*K) (universelle Gaskonstante)
                mw = 18.016 kg/kmol (Molekulargewicht des Wasserdampfes)
                AF = absolute Feuchte in g Wasserdampf pro m3 Luft

                Formeln:
                SDD(T) = 6.1078 * 10^((a*T)/(b+T))
                DD(r,T) = r/100 * SDD(T)
                r(T,TD) = 100 * SDD(TD) / SDD(T)
                TD(r,T) = b*v/(a-v) mit v(r,T) = log10(DD(r,T)/6.1078)
                AF(r,TK) = 10^5 * mw/R* * DD(r,T)/TK; AF(TD,TK) = 10^5 * mw/R* * SDD(TD)/TK
             */

            if ($temperature >= 0) {
                $a = 7.5;
                $b = 237.3;
            } else {
                $a = 7.6;
                $b = 240.7;
            }

            $SDD = function ($temperature) use ($a, $b)
            {
                $this->SendDebug('Sättitigungsdampfdruck', 6.1078 * pow(10, (($a * $temperature) / ($b + $temperature))) . ' hPa', 0);
                return 6.1078 * pow(10, (($a * $temperature) / ($b + $temperature)));
            };

            $DD = function ($humidity, $temperature) use ($SDD)
            {
                $this->SendDebug('Dampfdruck', $humidity / 100 * $SDD($temperature) . ' hPa', 0);
                return $humidity / 100 * $SDD($temperature);
            };

            $v = function ($humidity, $temperature) use ($DD)
            {
                return log10($DD($humidity, $temperature) / 6.1078);
            };

            $dewPoint = $b * $v($humidity, $temperature) / ($a - $v($humidity, $temperature));

            return $dewPoint;
        }

        private function CalculateMoldRisk(float $humidity, float $temperature): float
        {
            //DewPoint + ~3.3°C
            //Source https://sicherheitsingenieur.nrw/rechner/taupunkt-berechnen-schimmelgefahr/

            $dewPoint = $this->CalculateDewPoint($humidity, $temperature);
            $moldPoint = $dewPoint + 3.3;

            $this->SetAlert($moldPoint, $temperature);

            return $moldPoint;
        }

        private function SetAlert($moldPoint, $temperature)
        {
            //Set Alert true if it is false and moldpoint higher equal than temperature
            //Set Alert false if it is true and temperature is higher than moldpoint +1°C

            $alert = $this->GetValue('MoldAlert');

            $this->SendDebug('AlertValue', $alert, 0);
            $this->SendDebug('Moldpoint', $moldPoint, 0);
            $this->SendDebug('Temperature', $temperature, 0);

            if (!$alert && ($moldPoint >= $temperature)) {
                $this->SetValue('MoldAlert', true);
            } elseif ($alert && (($moldPoint + 1) < $temperature)) {
                $this->SetValue('MoldAlert', false);
            }
        }
    }