# Sistema de Gestão de Stand Automóvel

Sistema completo de gestão de stand automóvel desenvolvido em PHP e MySQL com três áreas distintas: pública, utilizadores registados e administração.

## 🚀 Funcionalidades

### Área Pública
- ✅ Visualização de veículos (sem informação de estado)
- ✅ Registo de novos utilizadores
- ✅ Login com opção "Remember Me"
- ✅ Recuperação de password via email ("Forgot Me")

### Área de Utilizadores Registados
- ✅ Dashboard personalizado
- ✅ Gestão de perfil com upload de foto
- ✅ Listagem de veículos com filtros (marca e ano)
- ✅ Visualização do estado dos veículos (disponível/indisponível/brevemente)
- ✅ Reserva de test drives (apenas 1 por dia/hora)
- ✅ Histórico de reservas

### Área de Administração
- ✅ Dashboard com estatísticas
- ✅ Gestão completa de veículos (inserir, editar, eliminar)
- ✅ Gestão de estado dos veículos
- ✅ Upload de fotos de veículos
- ✅ Listagem de utilizadores registados
- ✅ Visualização de reservas com filtros (data e veículo)

## 📋 Requisitos

- PHP >= 7.4
- MySQL >= 5.7
- Servidor web (Apache/Nginx)
- Extensões PHP: mysqli, gd, fileinfo

## 🛠️ Instalação

### 1. Clonar/Copiar Ficheiros

Copie a pasta `saw` para o diretório do seu servidor web.

### 2. Configurar Base de Dados

1. Crie uma base de dados MySQL:
```sql
CREATE DATABASE saw_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importe o ficheiro SQL:
```bash
mysql -u root -p saw_db < database.sql
```

Ou através do phpMyAdmin:
- Aceda ao phpMyAdmin
- Selecione a base de dados `saw_db`
- Vá ao separador "Importar"
- Escolha o ficheiro `database.sql`
- Clique em "Executar"

### 3. Configurar Aplicação

Edite o ficheiro `includes/config.php` e ajuste as seguintes configurações:

```php
// Configurações da Base de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Seu utilizador MySQL
define('DB_PASS', '');              // Sua password MySQL
define('DB_NAME', 'saw_db');

// Configurações de URL (ajustar conforme o ambiente)
define('BASE_URL', 'http://localhost/saw/public_html');
```

### 4. Configurar Permissões

Certifique-se que as pastas têm permissões de escrita:

```bash
chmod 755 public_html/images/veiculos
chmod 755 public_html/images/perfis
chmod 755 logs
```

## 🔐 Credenciais Padrão

### Administrador
- **Email:** admin@saw.pt
- **Password:** Admin123!

### Utilizadores de Teste
- **Email:** joao@example.com
- **Password:** Test123!

- **Email:** maria@example.com
- **Password:** Test123!

> ⚠️ **IMPORTANTE:** Altere a password do administrador após o primeiro login!

## 📁 Estrutura de Ficheiros

```
/saw/
├── public_html/              # Ficheiros públicos
│   ├── css/
│   │   └── style.css        # Estilos CSS
│   ├── images/
│   │   ├── veiculos/        # Fotos dos veículos
│   │   └── perfis/          # Fotos de perfil
│   ├── user/                # Área de utilizadores
│   ├── admin/               # Área de administração
│   ├── index.php            # Página inicial
│   ├── login.php
│   ├── registo.php
│   ├── esqueci_password.php
│   ├── reset_password.php
│   └── veiculos_publico.php
├── includes/                # Ficheiros de configuração
│   ├── config.php           # Configurações
│   ├── db.php               # Conexão BD
│   ├── auth.php             # Autenticação
│   ├── funcoes.php          # Funções gerais
│   ├── validacao.php        # Validação
│   ├── sanitizacao.php      # Sanitização
│   ├── session.php          # Gestão de sessões
│   └── email.php            # Envio de emails
├── logs/                    # Logs de erro
└── database.sql             # Script SQL
```

## 🎨 Design

O sistema utiliza um design moderno e responsivo com:
- Paleta de cores profissional (gradientes azul/roxo)
- Animações suaves
- Layout responsivo para mobile/tablet/desktop
- Componentes reutilizáveis (cards, forms, buttons, tables)
- Ícones emoji para melhor UX

## 🔒 Segurança

- ✅ Passwords encriptadas com bcrypt
- ✅ Proteção contra SQL Injection (prepared statements)
- ✅ Proteção contra XSS (sanitização de outputs)
- ✅ Validação de inputs no servidor
- ✅ Sessões seguras com regeneração de ID
- ✅ Tokens temporários para reset de password
- ✅ Verificação de permissões por área

## 📧 Configuração de Email

Para a recuperação de password funcionar em produção, configure um servidor SMTP ou use PHPMailer.

Edite `includes/email.php` para configurar o envio de emails conforme o seu ambiente.

## 🐛 Troubleshooting

### Erro de conexão à BD
- Verifique as credenciais em `includes/config.php`
- Certifique-se que o MySQL está a correr
- Verifique se a base de dados foi criada

### Imagens não aparecem
- Verifique as permissões das pastas `images/veiculos` e `images/perfis`
- Verifique se o `BASE_URL` está correto em `config.php`

### Erro ao fazer upload
- Verifique as permissões das pastas de imagens
- Verifique o `upload_max_filesize` e `post_max_size` no php.ini

## 📝 Notas

- O sistema está configurado para modo DEBUG. Em produção, altere `DEBUG_MODE` para `false` em `config.php`
- Os logs de erro são guardados em `logs/saw-error.log`
- Apenas 1 test drive pode ser marcado por dia/hora
- As fotos são limitadas a 5MB

## 🌐 Acesso

Após a instalação, aceda a:
- **Página inicial:** http://localhost/saw/public_html/
- **Área de admin:** http://localhost/saw/public_html/admin/
- **Área de utilizador:** http://localhost/saw/public_html/user/

## 📄 Licença

Este projeto foi desenvolvido para fins educacionais.

---

**Desenvolvido com ❤️ usando PHP e MySQL**
