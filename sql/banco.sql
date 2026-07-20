-- ============================================
-- FINANCINHAS - Banco de Dados PostgreSQL
-- ============================================

DROP TABLE IF EXISTS movimentacoes CASCADE;
DROP TABLE IF EXISTS investimentos CASCADE;
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

-- 5. INVESTIMENTOS
CREATE TABLE investimentos (
 id SERIAL PRIMARY KEY,
 usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
 tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('caixinha', 'acao', 'rendafixa')),
 nome VARCHAR(100) NOT NULL,
 valor_aplicado DECIMAL(10,2) DEFAULT 0,
 valor_atual DECIMAL(10,2) DEFAULT 0,
 data_retirada DATE DEFAULT NULL,
 funcao VARCHAR(100) DEFAULT NULL,
 taxa_juros DECIMAL(5,2) DEFAULT 0,
 data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- ÍNDICES PARA PERFORMANCE
-- ============================================
CREATE INDEX idx_movimentacoes_usuario ON movimentacoes(usuario_id);
CREATE INDEX idx_movimentacoes_data ON movimentacoes(data_movimento);
CREATE INDEX idx_contas_usuario ON contas(usuario_id);
CREATE INDEX idx_investimentos_usuario ON investimentos(usuario_id);
