<?php
class chat_ia {
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

    # conversas 
    public function estoricoDeConversas($contato)
    {
        $sql = "SELECT mensagem as eu , resposta as voce  FROM chat_ia WHERE resposta !='false' and contato = ':contato' ";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':contato', $contato);
        $stmt->execute();
        $this->dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->dados = json_encode($this->dados);
        return $this->dados;
    }
    public function salvarConversa($contato, $mensagem, $resposta , $estancia, $server_url, $apikey)
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
}

?>