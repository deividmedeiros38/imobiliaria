-- Imobiliária: esquema do banco (MySQL 5.7+ / MariaDB 10.3+)
-- Importe pelo phpMyAdmin (aba Importar) ou: mysql -u root < database/schema.sql
-- Os imóveis de exemplo têm "[DEMO]" no título e código DEMO-xxx: apague no painel antes de publicar.

CREATE DATABASE IF NOT EXISTS imobiliaria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE imobiliaria;

CREATE TABLE IF NOT EXISTS administradores (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(120) NOT NULL,
    email         VARCHAR(190) NOT NULL,
    senha_hash    VARCHAR(255) NOT NULL,
    ativo         TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login  DATETIME NULL,
    criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB;

-- Registro das tentativas de login para limitar força bruta
CREATE TABLE IF NOT EXISTS login_tentativas (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip         VARCHAR(45) NOT NULL,
    email      VARCHAR(190) NOT NULL,
    sucesso    TINYINT(1) NOT NULL DEFAULT 0,
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tent_ip (ip, criado_em),
    KEY idx_tent_email (email, criado_em)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias (
    id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome   VARCHAR(80) NOT NULL,
    slug   VARCHAR(100) NOT NULL,
    ativo  TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categoria_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS caracteristicas (
    id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome  VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_caracteristica_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS imoveis (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo           VARCHAR(20) NOT NULL,
    slug             VARCHAR(190) NOT NULL,
    titulo           VARCHAR(160) NOT NULL,
    descricao        TEXT NOT NULL,
    finalidade       ENUM('venda','aluguel') NOT NULL,
    categoria_id     INT UNSIGNED NOT NULL,
    preco            DECIMAL(12,2) NOT NULL,
    cidade           VARCHAR(80) NOT NULL,
    bairro           VARCHAR(80) NOT NULL,
    quartos          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    suites           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    banheiros        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    vagas            TINYINT UNSIGNED NOT NULL DEFAULT 0,
    area_construida  DECIMAL(10,2) NULL,
    area_terreno     DECIMAL(10,2) NULL,
    ativo            TINYINT(1) NOT NULL DEFAULT 1,
    destaque         TINYINT(1) NOT NULL DEFAULT 0,
    demo             TINYINT(1) NOT NULL DEFAULT 0,
    criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_imovel_codigo (codigo),
    UNIQUE KEY uq_imovel_slug (slug),
    KEY idx_imovel_busca (ativo, finalidade, cidade, bairro),
    KEY idx_imovel_preco (preco),
    KEY idx_imovel_destaque (ativo, destaque),
    KEY idx_imovel_criado (criado_em),
    CONSTRAINT fk_imovel_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS imovel_fotos (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    imovel_id  INT UNSIGNED NOT NULL,
    arquivo    VARCHAR(80) NOT NULL,          -- nome aleatório gerado pelo servidor
    legenda    VARCHAR(160) NULL,             -- usada como texto alternativo
    ordem      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    principal  TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_foto_imovel (imovel_id, ordem),
    CONSTRAINT fk_foto_imovel FOREIGN KEY (imovel_id) REFERENCES imoveis (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS imovel_caracteristicas (
    imovel_id         INT UNSIGNED NOT NULL,
    caracteristica_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (imovel_id, caracteristica_id),
    CONSTRAINT fk_ic_imovel FOREIGN KEY (imovel_id) REFERENCES imoveis (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ic_caract FOREIGN KEY (caracteristica_id) REFERENCES caracteristicas (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contatos (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    imovel_id          INT UNSIGNED NULL,
    nome               VARCHAR(120) NOT NULL,
    email              VARCHAR(190) NULL,
    telefone           VARCHAR(30) NULL,
    mensagem           TEXT NOT NULL,
    consentimento      TINYINT(1) NOT NULL DEFAULT 0,   -- LGPD: aceitou a política
    consentimento_em   DATETIME NULL,
    finalidade         VARCHAR(190) NOT NULL DEFAULT 'Atendimento sobre imóvel',
    ip                 VARCHAR(45) NULL,
    lido               TINYINT(1) NOT NULL DEFAULT 0,
    criado_em          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contato_lido (lido, criado_em),
    CONSTRAINT fk_contato_imovel FOREIGN KEY (imovel_id) REFERENCES imoveis (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS depoimentos (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome       VARCHAR(120) NOT NULL,
    texto      TEXT NOT NULL,
    ativo      TINYINT(1) NOT NULL DEFAULT 0,
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS configuracoes (
    chave  VARCHAR(60) NOT NULL,
    valor  TEXT NULL,
    PRIMARY KEY (chave)
) ENGINE=InnoDB;

-- ---------- Dados iniciais ----------
INSERT INTO configuracoes (chave, valor) VALUES
 ('nome_site',      'Luciano Rolim Negócios Imobiliários'),
 ('corretor_nome',  'Luciano Rolim'),
 ('creci',          '27743 J / 11656F'),
 ('telefone',       '(88) 98114-1527'),
 ('whatsapp',       '88981141527'),
 ('email',          'corretor.lucianorolim@gmail.com'),
 ('cidade_atuacao', 'Juazeiro do Norte e Região do Cariri - CE'),
 ('sobre',          'Corretor de Imóveis, iniciou suas atividades visando construir a sua história no mercado imobiliário da Região do Cariri, de forma sólida e confiável. Atuante no mercado imobiliário desde 2012 com ética, profissionalismo e transparência nas negociações.'),
 ('logotipo',       '')
ON DUPLICATE KEY UPDATE chave = chave;

INSERT INTO categorias (nome, slug) VALUES
 ('Casa','casa'),('Apartamento','apartamento'),('Terreno','terreno'),
 ('Comercial','comercial'),('Sítio e chácara','sitio-chacara')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

INSERT INTO caracteristicas (nome) VALUES
 ('Piscina'),('Churrasqueira'),('Área de serviço'),('Quintal'),('Varanda'),
 ('Cozinha planejada'),('Portão eletrônico'),('Próximo a escolas'),('Mobiliado')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Imóveis FICTÍCIOS de demonstração (sem endereço, sem fotos reais)
INSERT INTO imoveis (codigo, slug, titulo, descricao, finalidade, categoria_id, preco, cidade, bairro,
                     quartos, suites, banheiros, vagas, area_construida, area_terreno, destaque, demo) VALUES
 ('DEMO-001','demo-casa-3-quartos-juazeiro','[DEMO] Casa com 3 quartos',
  'Anúncio fictício para demonstração do sistema. Substitua por um imóvel real.','venda',
  (SELECT id FROM categorias WHERE slug='casa'), 350000.00,'Juazeiro do Norte','Centro',3,1,2,2,140.00,250.00,1,1),
 ('DEMO-002','demo-apartamento-2-quartos-crato','[DEMO] Apartamento com 2 quartos',
  'Anúncio fictício para demonstração do sistema. Substitua por um imóvel real.','aluguel',
  (SELECT id FROM categorias WHERE slug='apartamento'), 1500.00,'Crato','Centro',2,0,1,1,68.00,NULL,1,1),
 ('DEMO-003','demo-terreno-barbalha','[DEMO] Terreno plano',
  'Anúncio fictício para demonstração do sistema. Substitua por um imóvel real.','venda',
  (SELECT id FROM categorias WHERE slug='terreno'), 120000.00,'Barbalha','Bairro Exemplo',0,0,0,0,NULL,300.00,1,1),
 ('DEMO-004','demo-sala-comercial-juazeiro','[DEMO] Sala comercial',
  'Anúncio fictício para demonstração do sistema. Substitua por um imóvel real.','aluguel',
  (SELECT id FROM categorias WHERE slug='comercial'), 2200.00,'Juazeiro do Norte','Centro',0,0,1,1,45.00,NULL,0,1),
 ('DEMO-005','demo-casa-4-quartos-crato','[DEMO] Casa com 4 quartos e quintal',
  'Anúncio fictício para demonstração do sistema. Substitua por um imóvel real.','venda',
  (SELECT id FROM categorias WHERE slug='casa'), 520000.00,'Crato','Bairro Exemplo',4,2,3,3,210.00,400.00,0,1),
 ('DEMO-006','demo-sitio-barbalha','[DEMO] Sítio com área verde',
  'Anúncio fictício para demonstração do sistema. Substitua por um imóvel real.','venda',
  (SELECT id FROM categorias WHERE slug='sitio-chacara'), 480000.00,'Barbalha','Zona rural',3,1,2,4,120.00,5000.00,0,1)
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

INSERT IGNORE INTO imovel_caracteristicas (imovel_id, caracteristica_id)
SELECT i.id, c.id FROM imoveis i JOIN caracteristicas c
 WHERE i.codigo='DEMO-001' AND c.nome IN ('Quintal','Churrasqueira','Área de serviço');
