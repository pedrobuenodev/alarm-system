# Sistema de Alarmes Grupo Assist (Stack)

Sistema web para cadastro e manipulação de alarmes relacionados a equipamentos industriais.

Desenvolvido em **PHP 8.2 puro**, sem framework, seguindo o padrão **MVC** com separação clara de responsabilidades. Containerizado com **Docker** para facilitar a execução em qualquer ambiente.

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.2 (sem framework) |
| Frontend | Bootstrap 5.3 + JavaScript Vanilla |
| Banco de dados | MySQL 8.0 |
| Web server | Nginx Alpine |
| E-mail | MailHog (captura SMTP local) |
| Containers | Docker + Docker Compose |

---

## Pré-requisitos

- [Docker](https://www.docker.com/get-started/) ≥ 24
- [Docker Compose](https://docs.docker.com/compose/) ≥ 2 (já incluso no Docker Desktop)

> Não é necessário ter PHP, MySQL ou Nginx instalados na máquina.

---

## Como rodar

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/alarm-system.git
cd alarm-system

# 2. Suba os containers
docker compose up -d
```

Aguarde alguns segundos para o MySQL inicializar. O banco de dados é criado e migrado automaticamente na primeira execução via `database/migrations.sql`.

```bash
# 3. Verifique se todos os containers estão rodando
docker compose ps
```

Todos devem estar com status `Up`.

---

## Acessos

| Serviço | URL | Descrição |
|---|---|---|
| Aplicação | http://localhost:8080 | Sistema principal |
| MailHog | http://localhost:8025 | Visualizar e-mails enviados |
| MySQL | `localhost:3306` | Usuário: `alarm_user` / Senha: `alarm_pass` |

---

## Funcionalidades

### Equipamentos
CRUD completo com os campos: nome, número de série (único), tipo (Tensão / Corrente / Óleo) e data de cadastro. Equipamentos com alarmes vinculados não podem ser excluídos.

### Alarmes
CRUD completo com os campos: descrição, classificação (Urgente / Emergente / Ordinário), equipamento relacionado e data de cadastro.

### Manipulação de Alarmes

**Ativar / Desativar:**
- Alarme já ativo não pode ser ativado novamente
- Alarme já inativo não pode ser desativado novamente
- Ao ativar um alarme **Urgente**, um e-mail de notificação é enviado automaticamente para `abcd@abc.com.br`
- Alarme ativo não pode ser excluído

**Alarmes Atuados:**
- Listagem com data de entrada, data de saída, status, descrição do alarme e equipamento
- Ordenação clicável por qualquer coluna
- Filtro por descrição do alarme
- Exibe os **Top 3** alarmes que mais atuaram no sistema

### Audit Log
Todas as ações são registradas automaticamente (criação, edição, exclusão, ativação, desativação) com entidade, ID, IP e User-Agent. Visível no Dashboard.

---

## Estrutura do projeto

```
alarm-system/
├── docker-compose.yml
├── docker/
│   ├── php/Dockerfile
│   └── nginx/nginx.conf
├── database/
│   └── migrations.sql          # Schema completo — executado automaticamente
├── src/
│   ├── Config/
│   │   └── Database.php        # Conexão PDO (Singleton)
│   ├── Core/
│   │   ├── Router.php          # Roteador com suporte a parâmetros dinâmicos
│   │   ├── Request.php         # Wrapper de entrada HTTP
│   │   ├── Response.php        # Helpers para JSON, redirect e views
│   │   └── Controller.php      # Controller base
│   ├── Models/
│   │   ├── Equipment.php
│   │   ├── Alarm.php
│   │   ├── AlarmEvent.php      # Registro de ativações/desativações
│   │   └── AuditLog.php
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── EquipmentController.php
│   │   └── AlarmController.php
│   ├── Services/
│   │   ├── EmailService.php    # Envio via SMTP nativo (sem lib externa)
│   │   └── AuditService.php
│   └── Views/
│       ├── layout/             # Header e footer compartilhados
│       ├── dashboard/
│       ├── equipment/
│       ├── alarms/
│       └── errors/
└── public/
    ├── index.php               # Front controller único
    └── assets/
        ├── css/app.css
        └── js/app.js
```

---

## Decisões técnicas

- **MVC sem framework** — estrutura construída do zero conforme solicitado, sem scaffolding
- **PDO com prepared statements** em todas as queries — sem risco de SQL Injection
- **Soft delete** — registros não são apagados fisicamente, preservando histórico para auditoria
- **Method override via `_method`** — suporte a PUT e DELETE em formulários HTML padrão
- **Whitelist de colunas no ORDER BY** — ordenação dinâmica segura, sem possibilidade de injeção
- **EmailService sem dependência externa** — SMTP via `fsockopen` nativo do PHP; a implementação pode ser trocada por PHPMailer sem alterar a interface
- **MailHog no docker-compose** — captura todos os e-mails em desenvolvimento sem enviar de verdade; acesse em http://localhost:8025

---

## Comandos úteis

```bash
# Ver logs da aplicação
docker compose logs app

# Ver logs do banco
docker compose logs db

# Parar os containers
docker compose down

# Parar e remover o volume do banco (reseta os dados)
docker compose down -v

# Rebuildar após mudanças no Dockerfile
docker compose up -d --build
```
