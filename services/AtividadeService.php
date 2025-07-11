<?php
require_once __DIR__ . '/../repository/AtividadeRepository.php';

class AtividadeService {
    private $atividadeRepository;
    
    public function __construct() {
        $this->atividadeRepository = new AtividadeRepository();
    }
    
    public function criarAtividade($dados) {
        if (empty($dados['titulo']) || empty($dados['tipo']) || empty($dados['id_evento'])) {
            throw new Exception("Título, tipo e evento são obrigatórios");
        }
        
        return $this->atividadeRepository->criar($dados);
    }
    
    public function listarAtividades() {
        return $this->atividadeRepository->listar();
    }
    
    public function listarAtividadesPorEvento($id_evento) {
        if (empty($id_evento)) {
            throw new Exception("ID do evento é obrigatório");
        }
        
        return $this->atividadeRepository->listarPorEvento($id_evento);
    }
    
    public function buscarAtividadePorId($id) {
        if (empty($id)) {
            throw new Exception("ID é obrigatório");
        }
        
        $atividade = $this->atividadeRepository->buscarPorId($id);
        if (!$atividade) {
            throw new Exception("Atividade não encontrada");
        }
        
        return $atividade;
    }
    
    public function atualizarAtividade($id, $dados) {
        if (empty($id)) {
            throw new Exception("ID é obrigatório");
        }
        $atividade = $this->atividadeRepository->buscarPorId($id);
        if (!$atividade) {
            throw new Exception("Atividade não encontrada");
        }
        
        if (empty($dados['titulo']) || empty($dados['tipo']) || empty($dados['id_evento'])) {
            throw new Exception("Título, tipo e evento são obrigatórios");
        }
        
        return $this->atividadeRepository->atualizar($id, $dados);
    }
    
    public function deletarAtividade($id) {
        if (empty($id)) {
            throw new Exception("ID é obrigatório");
        }
        
        $atividade = $this->atividadeRepository->buscarPorId($id);
        if (!$atividade) {
            throw new Exception("Atividade não encontrada");
        }
        
        return $this->atividadeRepository->deletar($id);
    }
    
    public function obterEventos() {
        return $this->atividadeRepository->listarEventos();
    }
    
    public function obterTiposAtividade() {
        return [
            'Palestra',
            'Workshop',
            'Mesa Redonda',
            'Minicurso',
            'Apresentação',
            'Competição',
            'Networking'
        ];
    }
    
    public function contarAtividades() {
        $atividades = $this->atividadeRepository->listar();
        return count($atividades);
    }
    
    public function contarAtividadesPorEvento($id_evento) {
        $atividades = $this->atividadeRepository->listarPorEvento($id_evento);
        return count($atividades);
    }
}
?>
