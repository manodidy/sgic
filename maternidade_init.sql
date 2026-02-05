-- sql/maternidade_init.sql
-- Cria tabela de nascimentos para o módulo Materno-Infantil

CREATE TABLE IF NOT EXISTS nascimentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome_bebe VARCHAR(255) DEFAULT NULL,
  sexo ENUM('M','F','Outro','Desconhecido') NOT NULL DEFAULT 'Desconhecido',
  data_nascimento DATETIME DEFAULT NULL,
  mae_nome VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
