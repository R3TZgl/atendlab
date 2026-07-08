-- Criação da tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('admin', 'atendente') DEFAULT 'atendente',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserção do usuário administrador padrão
INSERT INTO usuarios (
    nome,
    email,
    senha,
    perfil,
    status
) VALUES (
    'Administrador',
    'admin@atendelab.com',
    '$2y$10$J9P2kU2BAMZ3TZcuxTsW4e1D/lka8EocYHzvyoOZmCNcWDQz3RuVC',
    'admin',
    'ativo'
);

-- Tabela de pessoas
CREATE TABLE pessoas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    documento VARCHAR(20) UNIQUE,
    telefone VARCHAR(20),
    email VARCHAR(100),
    curso VARCHAR(100),
    periodo VARCHAR(50),
    observacoes TEXT,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de tipos de atendimento
CREATE TABLE tipos_atendimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(100) NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Registros iniciais dos tipos de atendimento
INSERT INTO tipos_atendimentos (
    nome,
    descricao
) VALUES
    ('Orientação Acadêmica', 'Orientação Acadêmica'),
    ('Suporte Técnico', 'Suporte Técnico'),
    ('Atendimento Financeiro', 'Atendimento Financeiro'),
    ('Secretaria', 'Secretaria'),
    ('Outros', 'Outros');

-- Tabela de atendimentos
CREATE TABLE atendimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pessoa_id INT NOT NULL,
    tipo_atendimento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    descricao TEXT,
    data_atendimento DATETIME DEFAULT CURRENT_TIMESTAMP,
    horario_atendimento TIME,
    status ENUM(
        'aberto',
        'em_andamento',
        'concluido'
    ) DEFAULT 'aberto',
    observacao_final TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_atendimento_pessoa
        FOREIGN KEY (pessoa_id)
        REFERENCES pessoas(id),

    CONSTRAINT fk_atendimento_tipo
        FOREIGN KEY (tipo_atendimento_id)
        REFERENCES tipos_atendimentos(id),

    CONSTRAINT fk_atendimento_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
);
