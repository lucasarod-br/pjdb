CREATE TABLE Categoria (
    id_categoria INT PRIMARY KEY AUTO_INCREMENT,
    tipo VARCHAR(50) NOT NULL,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(255)
);

CREATE TABLE Local (
    id_local INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    campus VARCHAR(100) NOT NULL,
    sala VARCHAR(50) NOT NULL,
    capacidade INT NOT NULL
);

CREATE TABLE Evento (
    id_evento INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255),
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    id_categoria INT NOT NULL,
    id_local INT NOT NULL,
    foto LONGBLOB,
    FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria),
    FOREIGN KEY (id_local) REFERENCES Local(id_local)
);

CREATE TABLE Atividade (
    id_atividade INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    id_evento INT NOT NULL,
    FOREIGN KEY (id_evento) REFERENCES Evento(id_evento)
);

CREATE TABLE Organizador (
    id_organizador INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    vinculo VARCHAR(50) NOT NULL
);

CREATE TABLE Participante (
    id_participante INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    matricula VARCHAR(20) NOT NULL UNIQUE,
    curso VARCHAR(100) NOT NULL,
    data_inscricao DATE NOT NULL
);

CREATE TABLE Palestrante (
    id_palestrante INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    instituicao VARCHAR(100) NOT NULL
);

CREATE TABLE Certificado (
    id_certificado INT PRIMARY KEY AUTO_INCREMENT,
    data_emissao DATE NOT NULL,
    carga_horaria FLOAT NOT NULL,
    id_participante INT NOT NULL,
    id_evento INT NOT NULL,
    FOREIGN KEY (id_participante) REFERENCES Participante(id_participante),
    FOREIGN KEY (id_evento) REFERENCES Evento(id_evento)
);

CREATE TABLE Feedback (
    id_feedback INT PRIMARY KEY AUTO_INCREMENT,
    nota INT NOT NULL CHECK (nota >= 0 AND nota <= 10),
    comentario VARCHAR(255),
    id_participante INT NOT NULL,
    id_evento INT NOT NULL,
    FOREIGN KEY (id_participante) REFERENCES Participante(id_participante),
    FOREIGN KEY (id_evento) REFERENCES Evento(id_evento)
);

CREATE TABLE Organiza_Evento (
    id_organizador INT NOT NULL,
    id_evento INT NOT NULL,
    PRIMARY KEY (id_organizador, id_evento),
    FOREIGN KEY (id_organizador) REFERENCES Organizador(id_organizador),
    FOREIGN KEY (id_evento) REFERENCES Evento(id_evento)
);

CREATE TABLE Premiacao (
    id_evento INT NOT NULL,
    id_participante INT NOT NULL,
    colocacao INT NOT NULL,
    premio FLOAT NOT NULL,
    PRIMARY KEY (id_evento, id_participante),
    FOREIGN KEY (id_evento) REFERENCES Evento(id_evento),
    FOREIGN KEY (id_participante) REFERENCES Participante(id_participante)
);

CREATE TABLE Inscricao (
    id_participante INT NOT NULL,
    id_evento INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    data_inscricao DATE NOT NULL,
    PRIMARY KEY (id_participante, id_evento),
    FOREIGN KEY (id_participante) REFERENCES Participante(id_participante),
    FOREIGN KEY (id_evento) REFERENCES Evento(id_evento)
);

CREATE TABLE Atividade_Palestrante (
    id_atividade INT NOT NULL,
    id_palestrante INT NOT NULL,
    PRIMARY KEY (id_atividade, id_palestrante),
    FOREIGN KEY (id_atividade) REFERENCES Atividade(id_atividade),
    FOREIGN KEY (id_palestrante) REFERENCES Palestrante(id_palestrante)
);