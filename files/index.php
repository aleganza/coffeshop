<?php
    require('token.php'); // inserire token del bot telegram
    require('config.php'); // funzioni base
    require('../assets/databaseFunctions.php'); // funzioni per database

    file_put_contents("data.log", "we" . "\n", FILE_APPEND);

    try{
        $ngrokUrl = "https://6cd3-79-24-39-44.ngrok.io"; // inserire url di ngrok
        
        $bot = new Telegram($token);
        $jH = new jsonHandler($token);

        $bot->setWebhook($ngrokUrl);
        // ottengo json dal webhook come variabile php
        $webhookJson = $jH->getWebhookJson();

        // switch case per i comandi del bot
        $chatId = $jH->getChatId($webhookJson);
        $command = $jH->getText($webhookJson);

        // switch case per le operazioni del bot
        $callbackChatId = $jH->getCallbackChatId($webhookJson);
        $callback = $jH->getCallbackData($webhookJson);

        // prendo il chatId che mi servirà, che sia recuperato da un messaggio o da una callback da bottone
        $statusChatId = isset($chatId) ? $chatId : $callbackChatId;
        // ricevo status e step
        $status = getStatus($statusChatId);
        $step = getStep($statusChatId);

        // cancella ogni processo di registrazione che non è stato ultimato correttamente
        // non lo fa se ci troviamo nello status regitrati perchè non permetterebbe la registrazione
        if($status != "registrati")
            removeOldReg();

        /* file_put_contents("data.log", "stato: " . $status . "\n", FILE_APPEND);
        file_put_contents("data.log", "step: " . $step . "\n", FILE_APPEND); */

        switch($command){
            case '/start': {
                // aggiorno database con lo stato attuale
                setStatus($chatId, "start", 0);
                
                $textArray = array(
                    '🧾 Login',
                    '📝 Registrati',
                    '🖥 Entra come amministratore'
                );
                $callbackArray = array(
                    'login',
                    'registrati',
                    'amministratore'
                );

                $bot->sendKeyboard($chatId, $textArray, $callbackArray, 2, "👋 Benvenuto su ImmoBot!");

                $db->close();
                break;
            }
            // operazioni eseguibili da loggati 
            case '/functions': {
                if(checkLogged($chatId) == 1){

                    $textArray = array(
                        '🏢 Visualizza immobili',
                        '❌ Logout',
                        
                    );
                    $callbackArray = array(
                        'immobili', 
                        'logout'
                    );
                    $buttonNumber = count($textArray);
                    $bot->sendKeyboard($chatId, $textArray, $callbackArray, 2, "Funzioni");

                }else if(checkLogged($chatId) == 2){

                    $textArray = array(
                        '🏢 Visualizza immobili',
                        '❌ Logout',
                        
                    );
                    $callbackArray = array(
                        'immobili', 
                        'logout'
                    );
                    $buttonNumber = count($textArray);
                    $bot->sendKeyboard($chatId, $textArray, $callbackArray, 2, "Funzioni");

                }else{
                    $bot->sendMessage($chatId, "❌ Non sei loggato" . PHP_EOL . "➡️ Esegui /start per autenticarti");
                }

                break;
            }
            case '/help': {
                $msg = 'Comandi disponibili:'.PHP_EOL.'/go - Fai partire il bot'.PHP_EOL.'/help - Lista dei comandi disponibili';
                $bot->sendMessage($chatId, $msg);

                break;
            }
            case '/info': {
                $bot->sendMessage($chatId, 'Progetto Bot Telegram ' . PHP_EOL . ' Classe 5°I ' . PHP_EOL .' A.S. 2021-2022' . PHP_EOL . PHP_EOL .'© Alessio Ganzarolli ');

                break;
            }
            // se il comando non esiste
            default: {
                if ($command[0] == '/')
                    $bot->sendMessage($chatId, '❌ Comando non esistente');

                break;
            }
        }

        // bottoni premuti
        switch($callback){
            // autenticazione
            case 'login': {
                setStatus($callbackChatId, "login", 0);
                $bot->sendMessage($callbackChatId, "Inserisci codice fiscale");
                
                break;
            }
            case 'registrati': {
                
                setStatus($callbackChatId, "registrati", 0);
                $bot->sendMessage($callbackChatId, "Inserisci codice fiscale");

                break;
            }
            case 'amministratore': {
                setStatus($callbackChatId, "amministratore", 0);
                $bot->sendMessage($callbackChatId, "Inserisci username");
                
                break;
            }
            case 'logout': {
                setLogged($callbackChatId, 0);
                $bot->sendMessage($callbackChatId, "✅ Logout effettuato");

                break;
            }
        }

        switch($status){
            case 'registrati': {
                require('authentication/registrati.php');

                break;
            }
            case 'login': {
                require('authentication/login.php');

                break;
            }
            case 'amministratore': {
                require('authentication/amministratore.php');

                break;
            }
        }

    }catch(ErrorException $e){
        echo $e->getMessage();
    }
?>