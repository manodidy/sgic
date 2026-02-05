Guia Rápido — Build Tailwind e Composer (PDF/QR)

Tailwind (NPM):
1. No terminal (na pasta do projecto):
   npm install
2. Gerar CSS optimizado:
   npm run build:css
3. Em desenvolvimento, use:
   npm run watch:css

Composer (Dompdf + QR + PHPMailer):
1. Instale dependências:
   composer install

Notas:
- `generate_transfer.php` e o alerter usam `vendor/autoload.php`. As bibliotecas só funcionarão após `composer install`.
- Configure as variáveis SMTP em `config/settings.php` (`SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_SECURE`) ou deixe o sistema usar `mail()` como fallback.
- Substitua o hash de admin em `sql/init.sql` por um hash real gerado em PHP: php -r "echo password_hash('SENHA_ADMIN', PASSWORD_DEFAULT).PHP_EOL;"
- Depois de gerar CSS, `assets/css/tailwind.css` conterá a versão minificada pronta para produção.
