# Site imobiliário — Etapa 1 + Site público

PHP 8+, MySQL/MariaDB, PDO, JavaScript puro. Sem Composer, sem Node.

## O que funciona

### Fundação (etapa 1)
- Banco completo (`database/schema.sql`) com 6 imóveis fictícios `[DEMO]`
- Config separada, sem credenciais no código
- Login do admin: `password_hash`, CSRF, sessão com ID regenerado, limite de tentativas, logout só por POST
- Painel com resumo (contadores reais do banco)

### Site público (novo)
- Homepage profissional com hero, busca por código/finalidade e destaques
- Listagem de imóveis com filtros (código, finalidade, cidade, tipo, busca livre)
- Página de detalhe do imóvel (especificações, características, CTA WhatsApp)
- Página “Quem sou” inspirada em sites de corretores da região
- Formulário de contato com gravação na tabela `contatos` (LGPD)
- Design responsivo, paleta navy + dourado, botão flutuante de WhatsApp
- Layout limpo e funcional inspirado em sites profissionais de corretores

## Instalar no XAMPP
1. Copie a pasta `imobiliaria` para `C:\xampp\htdocs\`.
2. Inicie **Apache** e **MySQL** no painel do XAMPP.
3. No phpMyAdmin, aba **Importar**, envie `database/schema.sql`.
4. Copie `config/app.example.php` → `config/app.php` e `config/database.example.php` → `config/database.php`. Confira usuário/senha do MySQL (XAMPP: `root` e senha vazia).
5. Já existe um admin padrão no schema (ou crie pelo terminal):
   - Padrão: e-mail `admin@example.com` · senha `Admin12345!` (troque no painel em Conta)
   - Ou pelo terminal: `C:\xampp\php\php.exe bin\criar_admin.php`
   A senha é pedida no terminal (mínimo 10 caracteres) e gravada só como hash.
6. Abra `http://localhost/imobiliaria/public/` e `http://localhost/imobiliaria/public/admin/login.php`.

## Produção (hospedagem PHP/MySQL)
- `config/app.php`: `env` = `production` e `base_url` com `https://`.
- `config/database.php`: usuário próprio do banco, senha forte.
- Ideal: apontar o domínio para a pasta `public/`. Se a hospedagem só aceita `htdocs`, envie o projeto inteiro e use a pasta `public` como raiz do domínio, ou mantenha as pastas `app`, `config`, `includes`, `database`, `bin` com os `.htaccess` incluídos (negam acesso web). Teste abrindo `/config/app.php` no navegador: deve dar 403.
- Rode `bin/criar_admin.php` por SSH, ou crie o admin localmente e importe a linha da tabela `administradores`. Depois apague `bin/` do servidor.
- Apague os imóveis `[DEMO]` antes de publicar (ou marque `demo = 0` e troque os dados).
- Backup: exporte o banco (phpMyAdmin → Exportar) e baixe `public/uploads/` pelo menos uma vez por semana.

## Páginas públicas
| URL | Descrição |
|-----|-----------|
| `/` | Homepage com busca e destaques |
| `/imoveis.php` | Listagem + filtros |
| `/imovel.php?slug=...` | Detalhe do imóvel |
| `/sobre.php` | Quem sou |
| `/contato.php` | Formulário de contato |

## Próximas etapas
CRUD completo de imóveis/fotos/categorias/configurações no painel · upload de fotos · SEO avançado · acessibilidade refinada.

## Testes da etapa 1
Executados aqui (PHP 8.3 + MariaDB 10.11, servidor embutido do PHP): import do schema (duas vezes, sem duplicar), conexão PDO, criação de admin (senha curta/divergente recusadas), dashboard sem login redireciona, POST sem/com CSRF falso → 419, senha errada e e-mail inexistente com mesma mensagem, SQL injection no e-mail recusado, bloqueio na 6ª tentativa mesmo com senha correta, ID da sessão muda após login, cookie HttpOnly, logout por GET não desloga, logout por POST+CSRF funciona, cabeçalhos de segurança presentes, nenhum aviso PHP no log.

NÃO testados ainda: `.htaccess` (testei com servidor embutido, não Apache), expiração da sessão por inatividade, HTTPS/cookie `Secure`, celular/tablet, leitor de tela. Faça no XAMPP: abrir `/config/app.php` e `/includes/bootstrap.php` no navegador (esperado 403).
