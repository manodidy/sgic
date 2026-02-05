-- sql/init.sql - script de inicialização da base de dados
-- Execute em MySQL: CREATE DATABASE sgci CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci; USE sgci;

-- Tabela de utilizadores (padrão solicitado)
CREATE TABLE IF NOT EXISTS utilizadores (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nome VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  senha VARCHAR(255),
  perfil ENUM('Admin','Medico','Recepcionista') DEFAULT 'Recepcionista',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OBS: Em produção substitua o hash abaixo por um gerado com password_hash()
INSERT IGNORE INTO utilizadores (id, nome, email, senha, perfil) VALUES (1, 'Admin', 'admin@local', '$2y$10$REPLACE_WITH_REAL_HASH', 'Admin');

-- Pacientes (agora com id_clinico_local para registos provisórios e índices para pesquisa)
CREATE TABLE IF NOT EXISTS pacientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  nome_mae VARCHAR(255),
  data_nascimento DATE NULL,
  nuit VARCHAR(20) NULL,
  bi VARCHAR(20) NULL,
  id_clinico_local VARCHAR(50) UNIQUE NULL,
  idade INT NULL,
  temp DECIMAL(4,1) NULL,
  hr INT NULL,
  spo2 INT NULL,
  systolic INT NULL,
  triagem VARCHAR(50) NULL,
  status VARCHAR(50) DEFAULT 'nafila',
  foto_paciente VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (nuit),
  INDEX (id_clinico_local)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Consultas / Diagnósticos (ligadas a pacientes e a médicos)
CREATE TABLE IF NOT EXISTS consultas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  paciente_id INT NOT NULL,
  medico_id INT NULL,
  diagnostico VARCHAR(255) NULL,
  observacoes TEXT NULL,
  prescricao TEXT NULL,
  id_transacao_nacional VARCHAR(100) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
  FOREIGN KEY (medico_id) REFERENCES utilizadores(id) ON DELETE SET NULL,
  INDEX (id_transacao_nacional),
  INDEX (diagnostico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Histórico de Transferências / Interoperabilidade (XML + QR)
CREATE TABLE IF NOT EXISTS transferencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  transfer_id VARCHAR(100) UNIQUE NOT NULL,
  paciente_id INT NOT NULL,
  origem VARCHAR(255) NULL,
  destino VARCHAR(255) NULL,
  xml_content MEDIUMTEXT NULL,
  qr_payload TEXT NULL,
  created_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES utilizadores(id) ON DELETE SET NULL,
  INDEX (transfer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password resets (recuperação de conta)
CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES utilizadores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sessões ativas (opcional, útil para gestão e revogação)
CREATE TABLE IF NOT EXISTS sessions (
  session_id VARCHAR(128) PRIMARY KEY,
  user_id INT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
  data TEXT NULL,
  FOREIGN KEY (user_id) REFERENCES utilizadores(id) ON DELETE SET NULL,
  INDEX (user_id),
  INDEX (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Logs (ampliados com IP e user_agent para auditoria)
CREATE TABLE IF NOT EXISTS logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(user_id),
  INDEX(ip),
  FOREIGN KEY (user_id) REFERENCES utilizadores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nota: esquema expandido com tabelas clínicas e de interoperabilidade. Use o INSERT de exemplo mais acima para criar um admin real (substitua o hash).
