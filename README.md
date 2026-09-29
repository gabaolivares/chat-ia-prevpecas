# 🤖 Chat IA Prev-Peças — Captura de Leads com Inteligência Artificial

Chat flutuante com IA integrado a um **site WordPress institucional**, desenvolvido para **qualificação e captura segura de leads comerciais** em cliente real do setor de **CFTV** (Circuito Fechado de Televisão).

🔗 **Deploy:** [prevpecas.com.br](https://prevpecas.com.br)

---

## 📋 Índice

- [Sobre o projeto](#-sobre-o-projeto)
- [Funcionalidades](#-funcionalidades)
- [Tecnologias](#-tecnologias)
- [Segurança aplicada](#-segurança-aplicada)
- [Estrutura do projeto](#-estrutura-do-projeto)
- [Como configurar](#-como-configurar)
- [O que aprendi neste projeto](#-o-que-aprendi-neste-projeto)
- [Autor](#-autor)
- [Licença](#-licença)

---

## 🎯 Sobre o projeto

A **Prev-Peças** é uma empresa com mais de 25 anos de mercado na fabricação de estruturas metálicas para CFTV (postes, suportes, totens de vigilância e projetos customizados).

Este projeto foi desenvolvido para **automatizar o atendimento inicial** e a **qualificação de leads comerciais** por meio de um chat com Inteligência Artificial integrado ao site institucional da empresa.

O chat:
- Responde dúvidas técnicas e institucionais sobre a empresa
- Qualifica o visitante coletando **Nome, Empresa e Origem**
- Direciona orçamentos para o **setor comercial**
- Registra os leads de forma **segura e organizada**

---

## 🚀 Funcionalidades

- **Chat flutuante com IA** (integrado à API Groq — modelo LLM)
- **Qualificação automática de leads** (Nome, Empresa, Origem)
- **Coleta e gravação segura de leads** em log protegido
- **Painel administrativo** com login, filtros e exportação CSV
- **Design responsivo** com identidade visual do cliente
- **Integração transparente** com site WordPress existente
- **Aviso ético automático** (a IA não substitui profissionais da área)

---

## 🛠️ Tecnologias

| Camada | Tecnologia |
|---|---|
| **Back-end** | PHP 8.3 |
| **Front-end** | HTML5, CSS3, JavaScript (Fetch API) |
| **IA** | API Groq (modelo LLM) |
| **Integração** | WordPress institucional |
| **Infraestrutura** | HostGator, cPanel, HTTPS |

---

## 🔒 Segurança aplicada

Este projeto foi desenvolvido com foco em **segurança de aplicações web**, aplicando as seguintes boas práticas:

- ✅ **Chave da API em variável de ambiente** (`.env`) — nunca exposta no código
- ✅ **SSL verificado** em requisições cURL (`CURLOPT_SSL_VERIFYPEER => true`)
- ✅ **Validação de origem** (CSRF leve) — bloqueia requisições de outros domínios
- ✅ **Limite de tamanho de mensagem** (anti-abuso)
- ✅ **Limite de memória** de conversa por sessão
- ✅ **DOMPurify** na renderização das respostas da IA — proteção contra XSS
- ✅ **Log de leads fora da pasta pública** (`/logs/`) — inacessível via URL
- ✅ **`.htaccess`** bloqueando acesso a arquivos sensíveis
- ✅ **Painel administrativo** protegido por senha
- ✅ **Painel com `hash_equals()`** — previne timing attacks
- ✅ **`session_regenerate_id()`** no login do painel
- ✅ **Erros internos** não vazam para o cliente (vão para `error_log`)

---

## 📁 Estrutura do projeto
chat-ia-prevpecas/
├── chat_flutuante.php # Widget + endpoint da API da IA
├── env.php # Leitor do .env
├── leads.php # Painel administrativo de leads
├── leads_login.php # Login do painel
├── leads_export.php # Exportação CSV
├── .gitignore
├── .env.example
└── README.md

text

---

## ⚙️ Como configurar

### Pré-requisitos
- WordPress instalado e funcionando
- Acesso ao cPanel (ou FTP)
- Chave da API Groq ([console.groq.com/keys](https://console.groq.com/keys))

### Passo a passo

1. **Suba os arquivos** para a raiz do WordPress (`public_html/`)

2. **Crie o arquivo `.env`** na raiz do WordPress com:
GROQ_API_KEY=sua_chave_groq_aqui
IA_LOG_PATH=logs/leads_prevpecas.txt
IA_ORIGEM_PERMITIDA=https://seudominio.com.br

text

3. **Crie a pasta `logs/`** com um `.htaccess` bloqueando acesso:
```apache
Order Deny,Allow
Deny from all
Ative o chat no WordPress — adicione no footer.php do tema (ou via plugin Code Snippets):

php
add_action('wp_footer', function() {
    $caminho = ABSPATH . 'ia/chat_flutuante.php';
    if (file_exists($caminho)) include $caminho;
});
Acesse o painel de leads:

text
https://seudominio.com.br/leads_login.php
Configure a senha do painel em leads_login.php:

php
$SENHA_PAINEL = 'sua_senha_forte_aqui';
🧠 O que aprendi neste projeto
Este foi meu primeiro projeto desenvolvido para um cliente real em produção. Os principais aprendizados foram:

Integração com APIs externas (Groq) usando cURL no PHP, com tratamento de erros, timeout e verificação SSL

Segurança em aplicações web: proteção contra XSS com DOMPurify, CSRF leve, sanitização de saída, .env para credenciais

Arquitetura de log seguro: gravar leads fora da pasta pública (/logs/) e bloquear acesso via .htaccess

Captura estruturada de dados via IA: uso de tags ocultas ([LEAD: Nome | Empresa | Origem]) removidas antes de exibir ao usuário

Integração com WordPress: inclusão do chat via wp_footer sem quebrar o tema

Painel administrativo: leitura de log, filtros de busca, exportação CSV com encoding UTF-8 correto para Excel

Hospedagem compartilhada: configuração de .htaccess, permissões, cPanel

👨‍💻 Autor
Gabriel Olivares

🎓 Estudante de Técnico em Informática — IFSP Campus Guarulhos

💼 LinkedIn

📧 olivaresgaba@gmail.com

🐙 GitHub

📄 Licença
Este projeto está sob a licença MIT — consulte o arquivo LICENSE para mais detalhes.

⭐ Se este projeto foi útil para você, considere dar uma estrela!
