<?php
require_once __DIR__ . '/../repository/EventoRepository.php';

class EventoService {
    private $eventoRepository;
    
    public function __construct() {
        $this->eventoRepository = new EventoRepository();
    }
    
    public function criarEvento($dados) {
        // Validações
        if (empty($dados['nome']) || empty($dados['data_inicio']) || empty($dados['data_fim'])) {
            throw new Exception("Nome, data de início e data de fim são obrigatórios");
        }
        
        if ($dados['data_inicio'] > $dados['data_fim']) {
            throw new Exception("A data de início não pode ser maior que a data de fim");
        }
        
        if ($dados['data_inicio'] < date('Y-m-d')) {
            throw new Exception("A data de início não pode ser anterior a hoje");
        }
        
        // Validar foto se fornecida
        if (isset($dados['foto']) && !empty($dados['foto'])) {
            $this->validarFoto($dados['foto']);
        }
        
        return $this->eventoRepository->criar($dados);
    }
    
    public function listarEventos() {
        $eventos = $this->eventoRepository->listar();
        
        // Converter foto BLOB para base64 para cada evento
        foreach ($eventos as &$evento) {
            if ($evento['foto']) {
                $evento['foto'] = base64_encode($evento['foto']);
                $evento['has_foto'] = true;
            } else {
                $evento['has_foto'] = false;
            }
        }
        
        return $eventos;
    }
    
    public function buscarEventoPorId($id) {
        if (empty($id)) {
            throw new Exception("ID é obrigatório");
        }
        
        $evento = $this->eventoRepository->buscarPorId($id);
        if (!$evento) {
            throw new Exception("Evento não encontrado");
        }
        
        // Converter foto BLOB para base64 se existir
        if ($evento['foto']) {
            $evento['foto'] = base64_encode($evento['foto']);
            $evento['has_foto'] = true;
        } else {
            $evento['has_foto'] = false;
        }
        
        return $evento;
    }
    
    public function atualizarEvento($id, $dados) {
        if (empty($id)) {
            throw new Exception("ID é obrigatório");
        }
        
        // Verificar se o evento existe
        $evento = $this->eventoRepository->buscarPorId($id);
        if (!$evento) {
            throw new Exception("Evento não encontrado");
        }
        
        // Validações
        if (empty($dados['nome']) || empty($dados['data_inicio']) || empty($dados['data_fim'])) {
            throw new Exception("Nome, data de início e data de fim são obrigatórios");
        }
        
        if ($dados['data_inicio'] > $dados['data_fim']) {
            throw new Exception("A data de início não pode ser maior que a data de fim");
        }
        
        // Validar foto se fornecida
        if (isset($dados['foto']) && !empty($dados['foto'])) {
            $this->validarFoto($dados['foto']);
        }
        
        return $this->eventoRepository->atualizar($id, $dados);
    }
    
    public function deletarEvento($id) {
        if (empty($id)) {
            throw new Exception("ID é obrigatório");
        }
        
        // Verificar se o evento existe
        $evento = $this->eventoRepository->buscarPorId($id);
        if (!$evento) {
            throw new Exception("Evento não encontrado");
        }
        
        // Verificar se há inscrições confirmadas ou presentes no evento
        $inscricoesAtivas = $this->eventoRepository->verificarInscricoesAtivas($id);
        if ($inscricoesAtivas > 0) {
            throw new Exception("Não é possível excluir o evento. Existem {$inscricoesAtivas} inscrições ativas (confirmadas ou presentes). Cancele as inscrições primeiro.");
        }
        
        // Realizar exclusão em cascata
        return $this->eventoRepository->deletarComCascata($id);
    }
    
    public function obterCategorias() {
        return $this->eventoRepository->listarCategorias();
    }
    
    public function obterLocais() {
        return $this->eventoRepository->listarLocais();
    }
    
    public function obterEventosProximos() {
        $eventos = $this->eventoRepository->listar();
        
        $eventosProximos = array_filter($eventos, function($evento) {
            return $evento['data_inicio'] >= date('Y-m-d');
        });
        
        // Converter fotos para base64
        foreach ($eventosProximos as &$evento) {
            if ($evento['foto']) {
                $evento['foto'] = base64_encode($evento['foto']);
                $evento['has_foto'] = true;
            } else {
                $evento['has_foto'] = false;
            }
        }
        
        return array_slice($eventosProximos, 0, 5);
    }
    
    public function contarEventos() {
        $eventos = $this->eventoRepository->listar();
        return count($eventos);
    }
    
    /**
     * Validar dados da foto
     */
    private function validarFoto($foto) {
        // Verificar se é uma string válida (BLOB)
        if (!is_string($foto)) {
            throw new Exception("Formato de foto inválido");
        }
        
        // Verificar tamanho máximo (2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if (strlen($foto) > $maxSize) {
            throw new Exception("Arquivo de foto muito grande. Máximo 2MB");
        }
        
        // Verificar se é uma imagem válida usando finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($foto);
        
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WebP");
        }
    }
    
    /**
     * Processar foto do upload
     */
    public function processarFotoUpload($arquivo) {
        if (!isset($arquivo) || $arquivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Verificar tamanho
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($arquivo['size'] > $maxSize) {
            throw new Exception("Arquivo muito grande. Máximo 2MB");
        }
        
        // Verificar tipo
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];
        
        if (!in_array($arquivo['type'], $allowedTypes)) {
            throw new Exception("Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WebP");
        }
        
        // Ler conteúdo do arquivo
        return file_get_contents($arquivo['tmp_name']);
    }
    
    /**
     * Processar foto em base64
     */
    public function processarFotoBase64($base64String) {
        if (empty($base64String)) {
            return null;
        }
        
        // Remover prefixo data:image se existir
        $foto = preg_replace('#^data:image/\w+;base64,#i', '', $base64String);
        
        // Decodificar base64
        $fotoDecoded = base64_decode($foto);
        
        if ($fotoDecoded === false) {
            throw new Exception("Erro ao processar imagem");
        }
        
        // Validar a foto decodificada
        $this->validarFoto($fotoDecoded);
        
        return $fotoDecoded;
    }
}
?>