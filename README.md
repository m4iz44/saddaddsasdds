# Financinhas - Gestor Financeiro Pessoal

## Sobre o Projeto
O Financinhas é um sistema web simples e intuitivo para controle financeiro pessoal, desenvolvido como projeto final do curso de Programador Web com PHP. O sistema permite ao usuário gerenciar suas contas, registrar entradas e saídas, e acompanhar seu saldo total de forma prática.

## Tecnologias Utilizadas
- **Linguagem**: PHP 8.0+
- **Banco de Dados**: PostgreSQL 14+
- **Frontend**: HTML5, CSS3 puros
- **Arquitetura**: Estrutura simples dividida em `public/` (acessível pelo navegador) e `src/` (arquivos protegidos).

## Estrutura de Arquivos
- `/public/`: Ponto de entrada e páginas acessíveis (login, dashboard, views).
- `/src/`: Arquivos de configuração, conexão e funções.
- `/sql/`: Scripts de banco de dados.
- `/docs/`: Documentação e diagramas UML.

## Como Executar
1. Instale o PostgreSQL e crie um banco de dados chamado `financinhas`.
2. Configure o arquivo `.env` na raiz do projeto com as credenciais do seu banco de dados.
3. Execute o script `sql/banco.sql` no banco de dados criado.
4. Pelo terminal, inicie o servidor embutido do PHP apontando para a pasta `public`:
   ```bash
   php -S localhost:8080 -t public
   ```
5. Acesse no navegador: `http://localhost:8080/`
