<?php
    $text = $jH->getText($webhookJson);

    if($step == 0){
        if($text == USERNAME){
            $bot->sendMessage($statusChatId, "Inserisci password");
            setStatus($statusChatId, "amministratore", 1);
        }else{
            $bot->sendMessage($statusChatId, "❌ Username non corretto");
            $bot->sendMessage($statusChatId, "Inserisci username");
        }
    }
    if($step == 1){
        if($text == PASSWORD){
            $bot->sendMessage($statusChatId, "✅ Login come amministratore avvenuto!");
            setLogged($statusChatId, 2);
            setStatus($statusChatId, "start", 0);
            $bot->sendMessage($statusChatId, "➡️ Ora puoi eseguire /functions");
        }else{
            $bot->sendMessage($statusChatId, "❌ Password non corretta");
            $bot->sendMessage($statusChatId, "Inserisci password");
        }
    }

?>