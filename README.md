# SGCI - Sistema de Gestão Clínica e Interoperabilidade

Descrição rápida:
SGCI é uma aplicação web (PHP + MySQL + JS) para gestão clínica, filas e interoperabilidade via XML. Foco em segurança (PDO, hashing, logs) e triagem digital.

Instalação rápida:
1. Coloque a pasta `Porrada` em `htdocs` do XAMPP.
2. Importe o SQL: `mysql -u root -p < sql/init.sql` (ou use phpMyAdmin).
3. Edite `config/db.php` com credenciais reais.
4. Abra `http://localhost/Porrada/index.php`.

Tailwind:
- Rápido: use CDN (já usado em `includes/header.php`).
- Produção: instale `tailwindcss` via NPM e gere `assets/css/tailwind.css`.

Segurança e notas:
- Regenerar session_id no login (feito).
- Use HTTPS em produção.
- Ajuste regras de triagem para protocolos clínicos locais.

Módulo XML:
- `exportar.php` gera um XML compatível para intercâmbio com sistemas externos.

Contribuição:
- Por favor, crie uma branch e envie pull requests com testes e documentação.

Admin e Hash de Senha:
- Para criar um administrador inicial, gere um hash com PHP: `php -r "echo password_hash('SENHA_ADMIN', PASSWORD_DEFAULT);"` e substitua o valor em `sql/init.sql` no INSERT de exemplo.

Novos endpoints:
- `login_action.php` — processa login (POST email/senha)
- `processar_registo.php` — **Registo público desativado**. Use `admin_users.php` / `salvar_utilizador.php` para criação de contas pelo Administrador.
- `admin_users.php` / `salvar_utilizador.php` — criação de utilizadores por admin
- `admin_access_denied.php` — relatório de tentativas de acesso negado (Admin only)
- `api/queue.php` — endpoint JSON para atualizar a dashboard em tempo real

Segurança / Alertas:
- Sistema envia e-mail para ADMIN_EMAIL quando houver múltiplas tentativas de acesso negado num curto período (configurável em `config/settings.php`).
- Recomendação: configure SMTP / sendmail no servidor para e-mails funcionarem, ou ajuste `alert_admin_on_many_denials()` para integrar um serviço externo de envio (SendGrid/Mailgun).

