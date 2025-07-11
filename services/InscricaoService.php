<?php
require_once __DIR__ . '/../repository/InscricaoRepository.php';

class InscricaoService {
    private $inscricaoRepository;
    
    public function __construct() {
        $this->inscricaoRepository = new InscricaoRepository();
    }
    
    public function criarInscricao($dados) {
        if (empty($dados['id_participante']) || empty($dados['id_evento'])) {
            throw new Exception("Participante e evento são obrigatórios");
        }
        
        if ($this->inscricaoRepository->verificarInscricaoExistente($dados['id_participante'], $dados['id_evento'])) {
            throw new Exception("Participante já inscrito neste evento");
        }
        
        if (empty($dados['status'])) {
            $dados['status'] = 'Confirmada';
        }
        
        if (empty($dados['data_inscricao'])) {
            $dados['data_inscricao'] = date('Y-m-d');
        }
        
        return $this->inscricaoRepository->criar($dados);
    }
    
    public function listarInscricoes() {
        return $this->inscricaoRepository->listar();
    }
    
    public function listarInscricoesPorEvento($id_evento) {
        if (empty($id_evento)) {
            throw new Exception("ID do evento é obrigatório");
        }
        
        return $this->inscricaoRepository->listarPorEvento($id_evento);
    }
    
    public function listarInscricoesPorParticipante($id_participante) {
        if (empty($id_participante)) {
            throw new Exception("ID do participante é obrigatório");
        }
        
        return $this->inscricaoRepository->listarPorParticipante($id_participante);
    }
    
    public function buscarInscricaoPorId($id_participante, $id_evento) {
        if (empty($id_participante) || empty($id_evento)) {
            throw new Exception("ID do participante e do evento são obrigatórios");
        }
        
        $inscricao = $this->inscricaoRepository->buscarPorId($id_participante, $id_evento);
        if (!$inscricao) {
            throw new Exception("Inscrição não encontrada");
        }
        
        return $inscricao;
    }
    
    public function atualizarInscricao($id_participante, $id_evento, $dados) {
        if (empty($id_participante) || empty($id_evento)) {
            throw new Exception("ID do participante e do evento são obrigatórios");
        }
        
        $inscricao = $this->inscricaoRepository->buscarPorId($id_participante, $id_evento);
        if (!$inscricao) {
            throw new Exception("Inscrição não encontrada");
        }
        
        if (empty($dados['status'])) {
            throw new Exception("Status é obrigatório");
        }
        
        if (empty($dados['data_inscricao'])) {
            $dados['data_inscricao'] = $inscricao['data_inscricao'];
        }
        
        return $this->inscricaoRepository->atualizar($id_participante, $id_evento, $dados);
    }
    
    public function deletarInscricao($id_participante, $id_evento) {
        if (empty($id_participante) || empty($id_evento)) {
            throw new Exception("ID do participante e do evento são obrigatórios");
        }
        
        $inscricao = $this->inscricaoRepository->buscarPorId($id_participante, $id_evento);
        if (!$inscricao) {
            throw new Exception("Inscrição não encontrada");
        }
        
        return $this->inscricaoRepository->deletar($id_participante, $id_evento);
    }
    
    public function obterParticipantes() {
        return $this->inscricaoRepository->listarParticipantes();
    }
    
    public function obterEventos() {
        return $this->inscricaoRepository->listarEventos();
    }
    
    public function obterStatusDisponiveis() {
        return [
            'Confirmada',
            'Pendente',
            'Cancelada',
            'Presente',
            'Ausente'
        ];
    }
    
    public function contarInscricoes() {
        $inscricoes = $this->inscricaoRepository->listar();
        return count($inscricoes);
    }
    
    public function contarInscricoesPorEvento($id_evento) {
        $inscricoes = $this->inscricaoRepository->listarPorEvento($id_evento);
        return count($inscricoes);
    }
    
    public function contarInscricoesPorStatus($status) {
        $inscricoes = $this->inscricaoRepository->listar();
        
        return count(array_filter($inscricoes, function($inscricao) use ($status) {
            return $inscricao['status'] === $status;
        }));
    }
}
?>
