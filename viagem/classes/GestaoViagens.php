<?php
require_once 'auth_check.php';
require_once 'db_connect.php';

class GestaoViagens {
    private $db;
    private $usuario_id;

    public function __construct($usuario_id) {
        $this->db = Database::getInstance()->getConnection();
        $this->usuario_id = $usuario_id;
    }

    /**
     * Criar nova viagem
     */
    public function criarViagem($dados) {
        try {
            $this->db->beginTransaction();

            // Inserir a viagem
            $stmt = $this->db->prepare("
                INSERT INTO viagens (
                    usuario_id, titulo, descricao, 
                    data_inicio, data_fim, status
                ) VALUES (
                    :usuario_id, :titulo, :descricao, 
                    :data_inicio, :data_fim, :status
                )
            ");

            $stmt->execute([
                ':usuario_id' => $this->usuario_id,
                ':titulo' => $dados['titulo'],
                ':descricao' => $dados['descricao'],
                ':data_inicio' => $dados['data_inicio'],
                ':data_fim' => $dados['data_fim'],
                ':status' => 'planejamento'
            ]);

            $viagem_id = $this->db->lastInsertId();

            // Gerar dias do roteiro
            $this->gerarDiasRoteiro($viagem_id, $dados['data_inicio'], $dados['data_fim']);

            $this->db->commit();
            return $viagem_id;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao criar viagem: " . $e->getMessage());
            throw new Exception("Erro ao criar viagem. Por favor, tente novamente.");
        }
    }

    /**
     * Gerar dias do roteiro automaticamente
     */
    private function gerarDiasRoteiro($viagem_id, $data_inicio, $data_fim) {
        $data_atual = new DateTime($data_inicio);
        $data_final = new DateTime($data_fim);
        $ordem = 1;

        while ($data_atual <= $data_final) {
            $stmt = $this->db->prepare("
                INSERT INTO roteiros_dias (
                    viagem_id, data, ordem
                ) VALUES (
                    :viagem_id, :data, :ordem
                )
            ");

            $stmt->execute([
                ':viagem_id' => $viagem_id,
                ':data' => $data_atual->format('Y-m-d'),
                ':ordem' => $ordem
            ]);

            $data_atual->modify('+1 day');
            $ordem++;
        }
    }

    /**
     * Atualizar viagem existente
     */
    public function atualizarViagem($viagem_id, $dados) {
        try {
            // Verificar se a viagem pertence ao usuário
            if (!$this->verificarPropriedadeViagem($viagem_id)) {
                throw new Exception("Acesso não autorizado a esta viagem.");
            }

            $stmt = $this->db->prepare("
                UPDATE viagens 
                SET titulo = :titulo,
                    descricao = :descricao,
                    data_inicio = :data_inicio,
                    data_fim = :data_fim,
                    status = :status
                WHERE id = :id AND usuario_id = :usuario_id
            ");

            return $stmt->execute([
                ':id' => $viagem_id,
                ':usuario_id' => $this->usuario_id,
                ':titulo' => $dados['titulo'],
                ':descricao' => $dados['descricao'],
                ':data_inicio' => $dados['data_inicio'],
                ':data_fim' => $dados['data_fim'],
                ':status' => $dados['status']
            ]);

        } catch (PDOException $e) {
            error_log("Erro ao atualizar viagem: " . $e->getMessage());
            throw new Exception("Erro ao atualizar viagem. Por favor, tente novamente.");
        }
    }

    /**
     * Buscar viagem por ID com detalhes do roteiro
     */
    public function buscarViagem($viagem_id) {
        try {
            // Verificar se a viagem pertence ao usuário
            if (!$this->verificarPropriedadeViagem($viagem_id)) {
                throw new Exception("Acesso não autorizado a esta viagem.");
            }

            // Buscar dados da viagem com contagens
            $stmt = $this->db->prepare("
                SELECT v.*, 
                       COUNT(DISTINCT rd.id) as total_dias,
                       COUNT(DISTINCT ls.local_id) as total_locais
                FROM viagens v
                LEFT JOIN roteiros_dias rd ON v.id = rd.viagem_id
                LEFT JOIN locais_salvos ls ON rd.id = ls.roteiro_dia_id
                WHERE v.id = :id AND v.usuario_id = :usuario_id
                GROUP BY v.id
            ");

            $stmt->execute([
                ':id' => $viagem_id,
                ':usuario_id' => $this->usuario_id
            ]);

            $viagem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($viagem) {
                // Buscar dias do roteiro
                $viagem['dias'] = $this->buscarDiasRoteiro($viagem_id);
            }

            return $viagem;

        } catch (PDOException $e) {
            error_log("Erro ao buscar viagem: " . $e->getMessage());
            throw new Exception("Erro ao buscar detalhes da viagem.");
        }
    }

    /**
     * Listar todas as viagens do usuário
     */
    public function listarViagens($filtros = []) {
        try {
            $where = ["usuario_id = :usuario_id"];
            $params = [':usuario_id' => $this->usuario_id];

            // Aplicar filtros
            if (!empty($filtros['status'])) {
                $where[] = "status = :status";
                $params[':status'] = $filtros['status'];
            }

            if (!empty($filtros['data_inicio'])) {
                $where[] = "data_inicio >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }

            $sql = "
                SELECT v.*, 
                       COUNT(rd.id) as total_dias,
                       COUNT(DISTINCT ls.local_id) as total_locais
                FROM viagens v
                LEFT JOIN roteiros_dias rd ON v.id = rd.viagem_id
                LEFT JOIN locais_salvos ls ON rd.id = ls.roteiro_dia_id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY v.id
                ORDER BY v.data_inicio ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erro ao listar viagens: " . $e->getMessage());
            throw new Exception("Erro ao carregar lista de viagens.");
        }
    }

    /**
     * Excluir viagem
     */
    public function excluirViagem($viagem_id) {
        try {
            // Verificar se a viagem pertence ao usuário
            if (!$this->verificarPropriedadeViagem($viagem_id)) {
                throw new Exception("Acesso não autorizado a esta viagem.");
            }

            $stmt = $this->db->prepare("
                DELETE FROM viagens 
                WHERE id = :id AND usuario_id = :usuario_id
            ");

            return $stmt->execute([
                ':id' => $viagem_id,
                ':usuario_id' => $this->usuario_id
            ]);

        } catch (PDOException $e) {
            error_log("Erro ao excluir viagem: " . $e->getMessage());
            throw new Exception("Erro ao excluir viagem.");
        }
    }

    /**
     * Buscar dias do roteiro com locais
     */
    public function buscarDiasRoteiro($viagem_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT rd.*,
                       COUNT(ls.id) as total_locais,
                       GROUP_CONCAT(
                           CONCAT_WS('|',
                               ls.id,
                               l.nome,
                               l.tipo,
                               ls.hora_inicio,
                               ls.hora_fim,
                               ls.ordem,
                               ls.status
                           )
                           SEPARATOR ';;'
                       ) as locais_info
                FROM roteiros_dias rd
                LEFT JOIN locais_salvos ls ON rd.id = ls.roteiro_dia_id
                LEFT JOIN locais l ON ls.local_id = l.id
                WHERE rd.viagem_id = :viagem_id
                GROUP BY rd.id
                ORDER BY rd.data ASC, ls.ordem ASC
            ");

            $stmt->execute([':viagem_id' => $viagem_id]);
            $dias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Processar os locais de cada dia
            foreach ($dias as &$dia) {
                $dia['locais'] = [];
                if (!empty($dia['locais_info'])) {
                    $locais = explode(';;', $dia['locais_info']);
                    foreach ($locais as $local) {
                        list($id, $nome, $tipo, $hora_inicio, $hora_fim, $ordem, $status) = explode('|', $local);
                        $dia['locais'][] = [
                            'id' => $id,
                            'nome' => $nome,
                            'tipo' => $tipo,
                            'hora_inicio' => $hora_inicio,
                            'hora_fim' => $hora_fim,
                            'ordem' => $ordem,
                            'status' => $status
                        ];
                    }
                }
                unset($dia['locais_info']); // Remove o campo concatenado
            }

            return $dias;
        } catch (PDOException $e) {
            error_log("Erro ao buscar dias do roteiro: " . $e->getMessage());
            throw new Exception("Erro ao carregar o roteiro.");
        }
    }

    /**
     * Verificar se a viagem pertence ao usuário
     */
    private function verificarPropriedadeViagem($viagem_id) {
        $stmt = $this->db->prepare("
            SELECT 1 FROM viagens 
            WHERE id = :id AND usuario_id = :usuario_id
        ");
        
        $stmt->execute([
            ':id' => $viagem_id,
            ':usuario_id' => $this->usuario_id
        ]);

        return $stmt->fetch() !== false;
    }

    /**
     * Atualizar ordem dos locais em um dia
     */
    public function atualizarOrdemLocais($roteiro_dia_id, $ordens) {
        try {
            $this->db->beginTransaction();

            foreach ($ordens as $local_id => $ordem) {
                $stmt = $this->db->prepare("
                    UPDATE locais_salvos 
                    SET ordem = :ordem 
                    WHERE id = :local_id AND roteiro_dia_id = :roteiro_dia_id
                ");

                $stmt->execute([
                    ':ordem' => $ordem,
                    ':local_id' => $local_id,
                    ':roteiro_dia_id' => $roteiro_dia_id
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar ordem dos locais: " . $e->getMessage());
            throw new Exception("Erro ao atualizar ordem dos locais.");
        }
    }

    /**
     * Remover um local do roteiro
     */
    public function removerLocal($local_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM locais_salvos 
                WHERE id = :local_id
            ");

            return $stmt->execute([':local_id' => $local_id]);

        } catch (PDOException $e) {
            error_log("Erro ao remover local: " . $e->getMessage());
            throw new Exception("Erro ao remover local do roteiro.");
        }
    }

    /**
     * Adicionar um local a um dia do roteiro
     */
    public function adicionarLocal($dia_id, $local) {
        try {
            $this->db->beginTransaction();

            // Primeiro, verifica se o local já existe no banco
            $stmt = $this->db->prepare("
                SELECT id FROM locais 
                WHERE nome = :nome AND tipo = :tipo 
                  AND (latitude = :latitude OR (latitude IS NULL AND :latitude IS NULL))
                  AND (longitude = :longitude OR (longitude IS NULL AND :longitude IS NULL))
                  AND (endereco = :endereco OR (endereco IS NULL AND :endereco IS NULL))
                LIMIT 1
            ");

            $stmt->execute([
                ':nome' => $local['nome'],
                ':tipo' => $local['tipo'],
                ':latitude' => $local['latitude'],
                ':longitude' => $local['longitude'],
                ':endereco' => $local['endereco']
            ]);

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                $local_id = $resultado['id'];
            } else {
                // Se não existe, cria um novo local
                $stmt = $this->db->prepare("
                    INSERT INTO locais (
                        nome, tipo, latitude, longitude, endereco
                    ) VALUES (
                        :nome, :tipo, :latitude, :longitude, :endereco
                    )
                ");

                $stmt->execute([
                    ':nome' => $local['nome'],
                    ':tipo' => $local['tipo'],
                    ':latitude' => $local['latitude'],
                    ':longitude' => $local['longitude'],
                    ':endereco' => $local['endereco']
                ]);

                $local_id = $this->db->lastInsertId();
            }

            // Obtém a próxima ordem disponível
            $stmt = $this->db->prepare("
                SELECT COALESCE(MAX(ordem), 0) + 1 as proxima_ordem
                FROM locais_salvos
                WHERE roteiro_dia_id = :dia_id
            ");
            $stmt->execute([':dia_id' => $dia_id]);
            $ordem = $stmt->fetch(PDO::FETCH_ASSOC)['proxima_ordem'];

            // Adiciona o local ao roteiro
            $stmt = $this->db->prepare("
                INSERT INTO locais_salvos (
                    roteiro_dia_id, local_id, ordem,
                    hora_inicio, hora_fim, notas, status
                ) VALUES (
                    :dia_id, :local_id, :ordem,
                    :hora_inicio, :hora_fim, :notas, 'pendente'
                )
            ");

            $stmt->execute([
                ':dia_id' => $dia_id,
                ':local_id' => $local_id,
                ':ordem' => $ordem,
                ':hora_inicio' => $local['hora_inicio'],
                ':hora_fim' => $local['hora_fim'],
                ':notas' => $local['notas']
            ]);

            $local_salvo_id = $this->db->lastInsertId();

            $this->db->commit();
            return $local_salvo_id;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao adicionar local: " . $e->getMessage());
            throw new Exception("Erro ao adicionar local ao roteiro.");
        }
    }

    /**
     * Adicionar um novo dia ao roteiro
     */
    public function adicionarDiaRoteiro($viagem_id, $data) {
        try {
            // Verificar se a viagem pertence ao usuário
            if (!$this->verificarPropriedadeViagem($viagem_id)) {
                throw new Exception("Acesso não autorizado a esta viagem.");
            }

            // Obter a próxima ordem disponível
            $stmt = $this->db->prepare("
                SELECT COALESCE(MAX(ordem), 0) + 1 as proxima_ordem
                FROM roteiros_dias
                WHERE viagem_id = :viagem_id
            ");
            $stmt->execute([':viagem_id' => $viagem_id]);
            $ordem = $stmt->fetch(PDO::FETCH_ASSOC)['proxima_ordem'];

            // Inserir o novo dia
            $stmt = $this->db->prepare("
                INSERT INTO roteiros_dias (
                    viagem_id, data, ordem
                ) VALUES (
                    :viagem_id, :data, :ordem
                )
            ");

            $stmt->execute([
                ':viagem_id' => $viagem_id,
                ':data' => $data,
                ':ordem' => $ordem
            ]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            error_log("Erro ao adicionar dia ao roteiro: " . $e->getMessage());
            throw new Exception("Erro ao adicionar novo dia ao roteiro.");
        }
    }

    /**
     * Remover um dia do roteiro
     */
    public function removerDiaRoteiro($dia_id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM roteiros_dias 
                WHERE id = :dia_id
            ");

            return $stmt->execute([':dia_id' => $dia_id]);

        } catch (PDOException $e) {
            error_log("Erro ao remover dia do roteiro: " . $e->getMessage());
            throw new Exception("Erro ao remover dia do roteiro.");
        }
    }

    /**
     * Atualizar um local do roteiro
     */
    public function atualizarLocal($local_salvo_id, $local) {
        try {
            $stmt = $this->db->prepare("
                UPDATE locais_salvos 
                SET hora_inicio = :hora_inicio,
                    hora_fim = :hora_fim,
                    notas = :notas
                WHERE id = :local_salvo_id
            ");

            return $stmt->execute([
                ':local_salvo_id' => $local_salvo_id,
                ':hora_inicio' => $local['hora_inicio'],
                ':hora_fim' => $local['hora_fim'],
                ':notas' => $local['notas']
            ]);

        } catch (PDOException $e) {
            error_log("Erro ao atualizar local: " . $e->getMessage());
            throw new Exception("Erro ao atualizar informações do local.");
        }
    }
}
?>
