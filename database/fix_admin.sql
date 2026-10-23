-- Rode no phpMyAdmin se o login não funcionar (banco já importado antes).
USE imobiliaria;

INSERT INTO administradores (nome, email, senha_hash, ativo)
VALUES (
  'Administrador',
  'admin@example.com',
  '$2y$10$GZ3FU6pbft2uYpr4wuSdZu9rhpIQzjItTX1jCNB65z.fz5TVAFcfa',
  1
)
ON DUPLICATE KEY UPDATE
  senha_hash = VALUES(senha_hash),
  ativo = 1,
  nome = VALUES(nome);

-- Se ainda existir o e-mail antigo inválido:
UPDATE administradores
   SET email = 'admin@example.com',
       senha_hash = '$2y$10$GZ3FU6pbft2uYpr4wuSdZu9rhpIQzjItTX1jCNB65z.fz5TVAFcfa',
       ativo = 1
 WHERE email = 'admin@local';
