-- ============================================
-- FINANCINHAS - Banco de Dados PostgreSQL (Dump de Estrutura e Carga Inicial)
-- ============================================

DROP TABLE IF EXISTS movimentacoes CASCADE;
DROP TABLE IF EXISTS contas CASCADE;
DROP TABLE IF EXISTS categorias CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;

-- 1. USUÁRIOS
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  moeda VARCHAR(3) DEFAULT 'R$',
  mes_fechamento INTEGER DEFAULT 1,
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. CONTAS
CREATE TABLE contas (
  id SERIAL PRIMARY KEY,
  usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
  nome VARCHAR(50) NOT NULL,
  tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('fisico', 'corrente', 'poupanca')),
  saldo DECIMAL(10,2) DEFAULT 0,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. CATEGORIAS
CREATE TABLE categorias (
  id SERIAL PRIMARY KEY,
  usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
  nome VARCHAR(50) NOT NULL,
  tipo VARCHAR(10) NOT NULL CHECK (tipo IN ('entrada', 'saida'))
);

-- 4. MOVIMENTAÇÕES
CREATE TABLE movimentacoes (
  id SERIAL PRIMARY KEY,
  usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
  conta_id INTEGER REFERENCES contas(id) ON DELETE CASCADE,
  categoria_id INTEGER REFERENCES categorias(id),
  descricao VARCHAR(200) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  tipo VARCHAR(10) NOT NULL CHECK (tipo IN ('entrada', 'saida')),
  status VARCHAR(10) DEFAULT 'pago' CHECK (status IN ('pago', 'pendente')),
  recorrencia BOOLEAN DEFAULT FALSE,
  tipo_recorrencia VARCHAR(20) DEFAULT NULL,
  qtd_repeticoes INTEGER DEFAULT 0,
  data_termino DATE DEFAULT NULL,
  data_movimento DATE NOT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- ÍNDICES PARA PERFORMANCE
-- ============================================
CREATE INDEX idx_movimentacoes_usuario ON movimentacoes(usuario_id);
CREATE INDEX idx_movimentacoes_data ON movimentacoes(data_movimento);
CREATE INDEX idx_contas_usuario ON contas(usuario_id);

-- ============================================
-- DADOS INICIAIS DE TESTE / DEMONSTRAÇÃO
-- ============================================

-- Usuário Padrão (Email: usuario@financinhas.com | Senha: 123456)
INSERT INTO usuarios (id, nome, email, senha) VALUES 
(1, 'Usuário de Teste', 'usuario@financinhas.com', '$2y$12$WShKbbFwYwZieMeQsdDrRu9dg6MNrHl2rQSFfywls66NnWyNlD2qS');

SELECT setval('usuarios_id_seq', (SELECT MAX(id) FROM usuarios));

-- Contas do Usuário
INSERT INTO contas (id, usuario_id, nome, tipo, saldo) VALUES 
(1, 1, 'Carteira Principal', 'corrente', 500.00),
(2, 1, 'Conta Poupança', 'poupanca', 1000.00);

SELECT setval('contas_id_seq', (SELECT MAX(id) FROM contas));

-- Categorias do Usuário
INSERT INTO categorias (id, usuario_id, nome, tipo) VALUES 
(1, 1, 'Salário', 'entrada'),
(2, 1, 'Outras Receitas', 'entrada'),
(3, 1, 'Alimentação', 'saida'),
(4, 1, 'Moradia', 'saida'),
(5, 1, 'Transporte', 'saida'),
(6, 1, 'Lazer', 'saida'),
(7, 1, 'Outras Despesas', 'saida');

SELECT setval('categorias_id_seq', (SELECT MAX(id) FROM categorias));

-- Movimentações de Exemplo
INSERT INTO movimentacoes (id, usuario_id, conta_id, categoria_id, descricao, valor, tipo, status, data_movimento) VALUES 
(1, 1, 1, 1, 'Salário Mensal', 3500.00, 'entrada', 'pago', CURRENT_DATE),
(2, 1, 1, 3, 'Supermercado', 450.00, 'saida', 'pago', CURRENT_DATE),
(3, 1, 1, 4, 'Aluguel Residencial', 1200.00, 'saida', 'pago', CURRENT_DATE),
(4, 1, 1, 5, 'Combustível / Uber', 180.00, 'saida', 'pago', CURRENT_DATE);

SELECT setval('movimentacoes_id_seq', (SELECT MAX(id) FROM movimentacoes));
