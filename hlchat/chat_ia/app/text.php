<?php
require_once('chat.php');
$chat = new chat_ia();
$resposta = $chat->mensagemNaoRespondida('559886284233@s.whatsapp.net','helian','https://evolution-evolution-api.lzbrix.easypanel.host','9491D1652195-4990-B1BC-89227D7ACD31');
if($resposta == null){
 echo 'ok';
}else {
    echo $resposta;
}
?>