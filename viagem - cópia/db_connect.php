<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USERNAME,
                DB_PASSWORD,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        } catch(PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // Função para buscar usuário por email
    public function buscarUsuarioPorEmail($email) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return false;
        }
    }

    // Função para inserir novo usuário
    public function inserirUsuario($nome, $email, $senha) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)"
            );
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $hash);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao inserir usuário: " . $e->getMessage());
            return false;
        }
    }

    // Função para atualizar último acesso
    public function atualizarUltimoAcesso($usuario_id) {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = :id"
            );
            $stmt->bindParam(':id', $usuario_id);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar último acesso: " . $e->getMessage());
            return false;
        }
    }

    // Função para buscar viagens do usuário
    public function buscarViagensUsuario($usuario_id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM viagens WHERE usuario_id = :usuario_id ORDER BY data_inicio DESC"
            );
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao buscar viagens: " . $e->getMessage());
            return false;
        }
    }

    // Função para inserir nova viagem
    public function inserirViagem($usuario_id, $titulo, $descricao, $data_inicio, $data_fim) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO viagens (usuario_id, titulo, descricao, data_inicio, data_fim) 
                 VALUES (:usuario_id, :titulo, :descricao, :data_inicio, :data_fim)"
            );
            
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            error_log("Erro ao inserir viagem: " . $e->getMessage());
            return false;
        }
    }
}
?>
