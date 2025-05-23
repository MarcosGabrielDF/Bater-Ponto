-- 1. Tabela de empresas (chefes)
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    codigo_empresa VARCHAR(20) NOT NULL UNIQUE
);

-- 2. Tabela de funcionários
CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto VARCHAR(255),
    trabalhando BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

-- 3. Tabela de registros de trabalho
CREATE TABLE registros_trabalho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    data DATE NOT NULL,
    horas_trabalhadas DECIMAL(5,2) DEFAULT 0.00,
    horas_extras DECIMAL(5,2) DEFAULT 0.00,
    salario_hora DECIMAL(10,2) NOT NULL,
    valor_a_receber DECIMAL(10,2) GENERATED ALWAYS AS (
        (horas_trabalhadas + horas_extras) * salario_hora
    ) STORED,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id)
);
