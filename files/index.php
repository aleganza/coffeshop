<?php
    require('token.php'); // inserire token del bot telegram
    require('config.php'); // funzioni base
    require('../assets/databaseFunctions.php'); // funzioni per database

    /* file_put_contents("data.log", $var . "\n", FILE_APPEND); */

    try{
        $ngrokUrl = "https://17eb-176-246-74-223.eu.ngrok.io/Informatica/Github/coffeshop/files/index.php"; // inserire url di ngrok
        
        $bot = new Telegram($token);
        $jH = new jsonHandler($token);

        $bot->setWebhook($ngrokUrl); // ottengo json dal webhook come variabile php

        // chatId e testo (comando) di un messaggio
        $chatId = $jH->getChatId($webhookJson);
        $command = $jH->getText($webhookJson); // usato nello switch($command)

        // chatId e callback_query di un bottone premuto
        $callbackChatId = $jH->getCallbackChatId($webhookJson);
        $callback = $jH->getCallbackData($webhookJson); // usato nello switch($callback)

        // prendo la chatId, che provenga da un bottone premuto o da un messaggio
        $statusChatId = isset($chatId) ? $chatId : $callbackChatId;
        // prendo status e step
        $status = getStatus($statusChatId);
        $step = getStep($statusChatId);

        // cancella ogni processo di registrazione che non è stato ultimato correttamente
        // non lo fa se ci troviamo nello status registrati perchè significherebbe che qualcuno si sta registrando
        if($status != "registrati")
            removeOldReg();

        /* file_put_contents("data.log", "stato: " . $status . "\n", FILE_APPEND);
        file_put_contents("data.log", "step: " . $step . "\n", FILE_APPEND); */

        // switch per i comandi
        switch($command){
            case '/start': { // comando principale

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
            /* operazioni eseguibili da loggati
             * if cases:
             * 1 - loggato come utente
             * 2 - loggato come amministratore
             * else - non loggato
             */
            case '/functions': {
                if(checkLogged($chatId) == 1){

                    $textArray = array(
                        '🏢 Visualizza i tuoi immobili',
                        '❌ Logout',
                        
                    );
                    $callbackArray = array(
                        'tuoiImmobili',
                        'logout'
                    );
                    $buttonNumber = count($textArray);
                    $bot->sendKeyboard($chatId, $textArray, $callbackArray, 2, "Funzioni");

                }else if(checkLogged($chatId) == 2){

                    $textArray = array(
                        '🏢 Popolarità zone',
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
            case '/help': { // lista dei comandi
                $msg = 'Comandi disponibili:'.PHP_EOL.'/go - Fai partire il bot'.PHP_EOL.'/help - Lista dei comandi disponibili';
                $bot->sendMessage($chatId, $msg);

                break;
            }
            case '/info': { // info sul bot
                $bot->sendMessage($chatId, 'Progetto Bot Telegram ' . PHP_EOL . ' Classe 5°I ' . PHP_EOL .' A.S. 2021-2022' . PHP_EOL . PHP_EOL .'© Alessio Ganzarolli ');

                break;
            }
            default: { // se il comando non esiste
                if ($command[0] == '/')
                    $bot->sendMessage($chatId, '❌ Comando non esistente');

                break;
            }
        }

        /* // switch per i bottoni premuti. funzionamento:
         * quando un bottone viene premuto si finisce in questo switch
         * per ogni case setta lo stato e lo step (0 perchè è appena iniziato) di quella operazione
         * scrive all'utente e richiede di inserire l'informazione richiesta
         * per gli step successivi, il testimone passa allo switch($status)
         */
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

            // operazioni
            case 'tuoiImmobili': {
                $bot->sendMessage($callbackChatId, "da fare visualizzazione immobili");
                
            }
        }

        /* switch per il proseguimento di uno status
         * quando si è presenti in uno stato, lo switch cicla ogni step dove richiede le rispettive credenziali
         */
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