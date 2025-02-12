<?php
class chat_ia
{
    private $conexao;
    private $dados = [];
    public function __construct()
    {
        try {
            $this->conexao = new pdo('mysql:host=localhost;dbname=hlchat', 'root', '');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    // obiter informaçoes


    # conversas 
    public function estoricoDeConversas($contato)
    {
        $sql = "SELECT mensagem as eu , resposta as voce  FROM chat_ia WHERE resposta !='false' and resposta !='true' and  contato = :contato ";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':contato', $contato);
        $stmt->execute();
        $this->dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->dados = json_encode($this->dados);
        return $this->dados;
    }
    public function salvarConversa($contato, $mensagem, $resposta, $estancia, $server_url, $apikey)
    {
        $sql = "INSERT INTO chat_ia (contato, mensagem, resposta, estancia, server_url, apikey) VALUES (:contato, :mensagem, :resposta, :estancia, :server_url, :apikey)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':contato', $contato);
        $stmt->bindParam(':mensagem', $mensagem);
        $stmt->bindParam(':resposta', $resposta);
        $stmt->bindParam(':estancia', $estancia);
        $stmt->bindParam(':server_url', $server_url);
        $stmt->bindParam(':apikey', $apikey);
        $stmt->execute();
        return $this->conexao->lastInsertId();
    }

    public function mensagemNaoRespondida($contato, $estancia, $server_url, $apikey)
    {
        $sql = "SELECT mensagem as eu FROM chat_ia WHERE resposta = 'false' AND contato = :contato";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':contato', $contato);
        $stmt->execute();
        $this->dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Extrai apenas os valores das mensagens
        $mensagens = [];
        foreach ($this->dados as $mensagem) {
            $mensagens[] = $mensagem['eu'];
        }
        // Junta as mensagens em uma única string separada por vírgulas
        $resultado = implode(", ", $mensagens);
        // alterar false para true 
        $sqlUpdate = "UPDATE chat_ia SET resposta = 'true' WHERE resposta = 'false' AND contato = :contato";
        $stmtUpdate = $this->conexao->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':contato', $contato);
        $stmtUpdate->execute();
        // escrevar uma nova linha

        
        return $resultado;
    }

    public function ia($mensagem, $prompt, $estorico)
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer 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"
        ];

        $payload = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                [
                    "role" => "user",
                    "content" => $mensagem
                ],
                [
                    "role" => "system",
                    "content" => $prompt
                ],
                [
                    "role" => "system",
                    "content" => $estorico
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $data = json_decode($response, true);
            $content = $data['choices'][0]['message']['content'];
            return $content;
        }
    }
    // enviar mensagem 
    public function responder($resposta, $telefone, $instance, $server_url, $apikey)
    {
        $curl = curl_init();

        // Montando o payload corretamente usando json_encode para evitar erros de formatação
        $payload = json_encode([
            'number' => $telefone,
            'text' => $resposta
        ]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $server_url . '/message/sendText/' . $instance,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,  // Definindo um timeout para evitar travamentos
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'apikey: ' . $apikey
            ),
        ));

        $response = curl_exec($curl);

        // Verificando erros do cURL
        if (curl_errno($curl)) {
            file_put_contents('error_log.txt', 'cURL Error: ' . curl_error($curl) . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents('response_log.txt', $response . PHP_EOL, FILE_APPEND);
        }

        curl_close($curl);
    }
    public function verificarRespostaFalse($contato)
    {
        $sql = "SELECT COUNT(*) as total FROM chat_ia WHERE resposta = 'false' AND contato = :contato";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':contato', $contato);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    public function excluirRespostasTrue($contato)
    {
        $sql = "DELETE FROM chat_ia WHERE resposta = 'true' AND contato = :contato";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':contato', $contato);
        $stmt->execute();
        return $stmt->rowCount();
    }
}?>