<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/env.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pergunta'])) {

    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');

    $origem_permitida = env('IA_ORIGEM_PERMITIDA', '');
    $origem_recebida = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';

    if ($origem_permitida !== '' && $origem_recebida !== '') {
        $origem_base = parse_url($origem_recebida, PHP_URL_SCHEME) . '://' . parse_url($origem_recebida, PHP_URL_HOST);
        if (strcasecmp($origem_base, rtrim($origem_permitida, '/')) !== 0) {
            http_response_code(403);
            echo json_encode(["resposta" => "Requisição bloqueada por segurança."]);
            exit;
        }
    }

    $pergunta_do_usuario = trim($_POST['pergunta'] ?? '');

    if ($pergunta_do_usuario === '') {
        echo json_encode(["resposta" => "Por favor, digite alguma coisa."]);
        exit;
    }

    if (mb_strlen($pergunta_do_usuario) > 2000) {
        echo json_encode(["resposta" => "Sua mensagem é muito longa. Tente resumir em até 2000 caracteres."]);
        exit;
    }

    $chave_groq = env('GROQ_API_KEY');
    if (empty($chave_groq)) {
        error_log('[PrevPeças IA] GROQ_API_KEY não configurada no .env');
        echo json_encode(["resposta" => "Erro de configuração no servidor. Tente novamente mais tarde."]);
        exit;
    }

    $url = "https://api.groq.com/openai/v1/chat/completions";

    $super_texto = <<<EOT
# PAPEL E IDENTIDADE
Você é o assistente virtual oficial da **Prev-Peças**, empresa especializada há mais de 25 anos na fabricação de estruturas metálicas para CFTV (postes, suportes, totens de vigilância e projetos customizados). Sua missão é atender os clientes de forma educada, breve, direta, sem enrolação, prestativa e profissional, tirando dúvidas técnicas/institucionais e qualificando os contatos antes de encaminhá-los ao setor comercial. Você pode ser mais técnico e falar mais se for preciso caso o cliente tenha dúvidas, mas para pedir informações de cadastro, seja breve e direto. Lembrando que você só pode falar sobre a Prev-Peças e tudo o que estiver envolvido que foi mandado aqui, sem desviar do foco que é direcionar o orçamento para o setor comercial, tirar dúvidas sobre a Prev e derivados, etc. Caso o assunto tente ser desviado, brinque com esse assunto já direcionando e relacionando a algo da PrevPeças.

---

# ETAPA 1: COLETA OBRIGATÓRIA DE INFORMAÇÕES (REGRA ABSOLUTA)
Antes de aprofundar qualquer atendimento avançado, tirar dúvidas específicas sobre projetos ou direcionar para orçamentos, você **DEVE obrigatoriamente solicitar e confirmar** as três informações a seguir:

1. **Nome completo do cliente**
2. **Nome da Empresa**
3. **Como/Onde conheceu a Prev-Peças** (ex.: indicação, Google, redes sociais...)

> **REGRA DE ETIQUETA OCULTA:**
> Apenas quando você possuir o **Nome**, a **Empresa** e a **Origem** (Como conheceu), insira no FINAL da sua resposta a tag formatada exatamente assim:
> `[LEAD: Nome | Empresa | Origem]`
> Se algum dado não for informado, preencha com "Não informado" dentro da tag. Exemplo: `[LEAD: Gabriel Olivares | MONCORP | Google]`

---

# ETAPA 2: TRATAMENTO DE ORÇAMENTOS E PREÇOS
Se o cliente mencionar o interesse em **orçamento, cotação, preços, valores, ou negociação comercial** de postes, suportes, totens ou estruturas customizadas:

1. **Esclarecimento Institucional:** Informe gentilmente e seja breve que a definição de preços, valores finais e discussões detalhadas sobre especificações comerciais dos produtos são tratadas exclusivamente com o **Setor Comercial**.
2. **Direcionamento por E-mail:** Indique brevemente ao cliente que envie os detalhes do projeto ou a solicitação de orçamento para o e-mail oficial da empresa: **comercial@prevpecas.com.br** (ou pelo WhatsApp da equipe comercial: **(11) 94742-8089**).
3. **Aviso Interno:** Confirme se o cliente já enviou as informações da Etapa 1 (Nome, Empresa, Origem) para que a equipe comercial possa dar continuidade ao atendimento com prioridade.
4. **Foque em ser direto sem ficar enrolando e repetindo as mesmas coisas, não fale muito. Peças desenhos também para orçamento**

---

# ETAPA 3: DÚVIDAS E PROCESSO INSTITUCIONAL (BASE DE CONHECIMENTO)
Você deve estar **sempre disponível e proativo** para tirar dúvidas sobre a história da empresa, seus diferenciais, soluções por segmento e processos de fabricação. Utilize os fatos abaixo:

### Sobre a Prev-Peças:
- **Experiência:** Mais de 25 anos de mercado.
- **Histórico:** Mais de 100 empresas atendidas em nível nacional e mais de 6.000 estruturas fabricadas.
- **Significado do nome PREV:**
  - **P** – Prevenção
  - **R** – Resistência
  - **E** – Excelência
  - **V** – Valor

### Processos Industriais e Qualidade:
- Especializada em **corte a laser**, **soldas especiais**, **galvanização** e **pintura eletrostática**, garantindo acabamento superior, alta resistência e durabilidade contra intempéries.

### Segmentos Atendidos:
1. **Condomínios:** Postes, suportes e estruturas para monitoramento residencial e comercial com excelente acabamento e segurança.
2. **Indústrias:** Estruturas metálicas robustas para suportar ambientes com condições severas.
3. **Telecom:** Postes, suportes e estruturas para equipamentos de telecomunicações e redes de comunicação.

### Produtos Principais:
- Postes flangeados (2M, 3M, etc.) e postes bipartidos (ex.: 8M).
- Postes para controle de acesso e cancelas de shopping.
- Postes e braços avançados para câmeras Dome.
- Totens de vigilância (ex.: Totem 3M).
- Estruturas de elevação para transformadores e soluções customizadas sob medida.

### Dados de Contato e Localização:
- **Endereço:** Rua Itaúna do Sul, 27 - Vila Rio, Guarulhos - SP.
- **WhatsApp:** (11) 94742-8089
- **Site:** https://prevpecas.com.br/
- **E-mail Comercial:** comercial@prevpecas.com.br

---

# TOM DE VOZ E COMPORTAMENTO
- **Tom de voz:** Profissional, técnico, acolhedor, transparente e direto.
- **Proatividade:** Termine sempre as respostas perguntando se o cliente tem mais alguma dúvida sobre as estruturas, processos de fabricação ou se precisa de auxílio para enviar o e-mail ao comercial.

# O QUE NÃO FAZER
- **Pedir CNPJ!**
- **Escrever muito**
EOT;

    if (!isset($_SESSION['memoria_chat']) || !is_array($_SESSION['memoria_chat'])) {
        $_SESSION['memoria_chat'] = [];
    }

    if (count($_SESSION['memoria_chat']) > 20) {
        $_SESSION['memoria_chat'] = array_slice($_SESSION['memoria_chat'], -20);
    }

    $mensagens_api = [
        ["role" => "system", "content" => $super_texto],
    ];

    foreach ($_SESSION['memoria_chat'] as $msg) {
        $role = $msg['role'] ?? '';
        $role = ($role === 'model' || $role === 'assistant') ? 'assistant' : 'user';

        $content = '';
        if (isset($msg['content'])) {
            $content = trim($msg['content']);
        } elseif (isset($msg['parts'][0]['text'])) {
            $content = trim($msg['parts'][0]['text']);
        }

        if ($content !== '') {
            $mensagens_api[] = ["role" => $role, "content" => $content];
        }
    }

    $mensagens_api[] = ["role" => "user", "content" => $pergunta_do_usuario];

    $dados = [
        "model"       => "openai/gpt-oss-20b",
        "messages"    => $mensagens_api,
        "temperature" => 0.7,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $chave_groq,
        ],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($dados),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 20,
    ]);

    $resultado = curl_exec($ch);
    $erro_curl = curl_error($ch);
    curl_close($ch);

    if ($erro_curl) {
        error_log('[PrevPeças IA] Erro cURL: ' . $erro_curl);
        echo json_encode(["resposta" => "Não foi possível conectar ao serviço de IA agora. Tente novamente em instantes."]);
        exit;
    }

    $retorno_json = json_decode($resultado, true);

    if (isset($retorno_json['choices'][0]['message']['content'])) {
        $resposta = $retorno_json['choices'][0]['message']['content'];

        if (preg_match('/\[LEAD:\s*(.*?)\]/is', $resposta, $matches_tag)) {
            $conteudo_lead = $matches_tag[1];

            $partes = explode('|', $conteudo_lead);

            $nome    = isset($partes[0]) && trim($partes[0]) !== '' ? trim($partes[0]) : 'Não informado';
            $empresa = isset($partes[1]) && trim($partes[1]) !== '' ? trim($partes[1]) : 'Não informado';
            $origem  = isset($partes[2]) && trim($partes[2]) !== '' ? trim($partes[2]) : 'Não informado';

            $caminho_log_relativo = env('IA_LOG_PATH', 'logs/leads_prevpecas.txt');
            $ficheiro_log = __DIR__ . '/../' . ltrim($caminho_log_relativo, '/');

            $data_hora = date('d/m/Y H:i:s');
            $registo   = "[{$data_hora}] NOME: {$nome} | EMPRESA: {$empresa} | ORIGEM: {$origem}" . PHP_EOL;

            $pasta_log = dirname($ficheiro_log);
            if (!is_dir($pasta_log)) {
                @mkdir($pasta_log, 0755, true);
            }

            @file_put_contents($ficheiro_log, $registo, FILE_APPEND | LOCK_EX);
        }

        $resposta_exibicao = preg_replace('/\[LEAD:.*?\]/is', '', $resposta);
        $resposta_exibicao = trim($resposta_exibicao);

        $_SESSION['memoria_chat'][] = ["role" => "user",      "content" => $pergunta_do_usuario];
        $_SESSION['memoria_chat'][] = ["role" => "assistant", "content" => $resposta_exibicao];

        if (count($_SESSION['memoria_chat']) > 20) {
            $_SESSION['memoria_chat'] = array_slice($_SESSION['memoria_chat'], -20);
        }

        echo json_encode(["resposta" => $resposta_exibicao]);
        exit;
    }

    $erro_detalhe = $retorno_json['error']['message'] ?? 'Retorno inválido da API.';
    error_log('[PrevPeças IA] Erro Groq: ' . $erro_detalhe);
    echo json_encode(["resposta" => "Não foi possível obter uma resposta agora. Tente novamente."]);
    exit;
}
?>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>

<style>
    :root {
        --prev-primaria: #0a3d62;
        --prev-primaria-clara: #1e5f8c;
        --prev-destaque: #e55039;
        --prev-destaque-clara: #f4715c;
        --prev-bg-janela: #ffffff;
        --prev-bg-chat: #f4f6f9;
        --prev-texto-escuro: #2f3640;
        --prev-texto-claro: #ffffff;
        --prev-borda: #e2e8f0;
        --prev-radius: 20px;
    }

    .ipp-btn {
        position: fixed; bottom: 25px; right: 25px;
        background: linear-gradient(135deg, var(--prev-primaria-clara), var(--prev-primaria));
        color: var(--prev-texto-claro); border: none; border-radius: 50%;
        width: 64px; height: 64px; cursor: pointer;
        box-shadow: 0 10px 25px rgba(10, 61, 98, 0.4);
        z-index: 999999; display: flex; align-items: center; justify-content: center;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .ipp-btn:hover { transform: translateY(-4px) scale(1.06); box-shadow: 0 14px 30px rgba(10, 61, 98, 0.5); }
    .ipp-btn::after {
        content: ''; position: absolute; inset: 0; border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(10, 61, 98, 0.55);
        animation: ipp-pulse 2.2s infinite;
    }
    @keyframes ipp-pulse {
        0%   { box-shadow: 0 0 0 0 rgba(10, 61, 98, 0.45); }
        70%  { box-shadow: 0 0 0 16px rgba(10, 61, 98, 0); }
        100% { box-shadow: 0 0 0 0 rgba(10, 61, 98, 0); }
    }
    .ipp-btn svg { width: 28px; height: 28px; transition: transform 0.25s ease, opacity 0.15s ease; }
    .ipp-btn .ipp-icon-close { position: absolute; opacity: 0; transform: rotate(-45deg) scale(0.6); }
    .ipp-btn.aberto .ipp-icon-chat { opacity: 0; transform: rotate(45deg) scale(0.6); }
    .ipp-btn.aberto .ipp-icon-close { opacity: 1; transform: rotate(0) scale(1); }
    .ipp-badge {
        position: absolute; top: -2px; right: -2px; width: 16px; height: 16px;
        background: #78e08f; border: 2px solid #fff; border-radius: 50%;
    }

    .ipp-window {
        position: fixed; bottom: 105px; right: 25px; width: 370px; max-width: 92vw;
        background-color: var(--prev-bg-janela); border-radius: var(--prev-radius);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
        display: flex; flex-direction: column; z-index: 999999; overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        opacity: 0; transform: translateY(16px) scale(0.97); pointer-events: none;
        transition: opacity 0.25s ease, transform 0.25s ease;
    }
    .ipp-window.aberta { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }

    .ipp-header {
        background: linear-gradient(135deg, var(--prev-primaria), var(--prev-primaria-clara));
        color: var(--prev-texto-claro); padding: 18px 18px;
        display: flex; align-items: center; gap: 12px;
        border-bottom: 3px solid var(--prev-destaque);
    }
    .ipp-avatar {
        width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--prev-destaque-clara), var(--prev-destaque));
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .ipp-header-info { flex-grow: 1; min-width: 0; }
    .ipp-header-title { font-size: 15px; font-weight: 700; }
    .ipp-header-status { font-size: 12px; opacity: 0.85; display: flex; align-items: center; gap: 5px; margin-top: 2px; }
    .ipp-status-dot { width: 7px; height: 7px; background-color: #78e08f; border-radius: 50%; display: inline-block; box-shadow: 0 0 0 2px rgba(120,224,143,0.3); }
    .ipp-fechar {
        background: rgba(255,255,255,0.12); border: none; color: var(--prev-texto-claro);
        width: 30px; height: 30px; border-radius: 50%; cursor: pointer; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; transition: background 0.2s;
    }
    .ipp-fechar:hover { background: rgba(255,255,255,0.25); }
    .ipp-fechar svg { width: 14px; height: 14px; }

    .ipp-history {
        padding: 18px 15px; height: 360px; overflow-y: auto;
        background-color: var(--prev-bg-chat); display: flex;
        flex-direction: column; gap: 12px;
    }
    .ipp-history::-webkit-scrollbar { width: 6px; }
    .ipp-history::-webkit-scrollbar-track { background: transparent; }
    .ipp-history::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .ipp-linha { display: flex; align-items: flex-end; gap: 8px; animation: ipp-surgir 0.25s ease; }
    .ipp-linha.usuario { justify-content: flex-end; }
    @keyframes ipp-surgir { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    .ipp-mini-avatar {
        width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--prev-primaria-clara), var(--prev-primaria));
        display: flex; align-items: center; justify-content: center; font-size: 10px; color: #fff; font-weight: 700;
    }

    .ipp-msg {
        padding: 11px 15px; font-size: 14px; line-height: 1.5; max-width: 78%;
    }
    .ipp-msg-usuario {
        background: linear-gradient(135deg, var(--prev-destaque-clara), var(--prev-destaque));
        color: var(--prev-texto-claro); border-radius: 16px 16px 2px 16px;
        box-shadow: 0 3px 8px rgba(229, 80, 57, 0.25);
    }
    .ipp-msg-ia {
        background: var(--prev-bg-janela); color: var(--prev-texto-escuro);
        border-radius: 16px 16px 16px 2px; border: 1px solid var(--prev-borda);
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .ipp-msg-ia p { margin: 0 0 8px 0; }
    .ipp-msg-ia p:last-child { margin: 0; }

    .ipp-digitando { align-self: flex-start; display: none; margin-left: 34px; }
    .ipp-digitando .ipp-msg-ia { display: flex; gap: 4px; padding: 12px 16px; }
    .ipp-ponto {
        width: 6px; height: 6px; border-radius: 50%; background: var(--prev-primaria);
        animation: ipp-piscar 1.2s infinite ease-in-out;
    }
    .ipp-ponto:nth-child(2) { animation-delay: 0.15s; }
    .ipp-ponto:nth-child(3) { animation-delay: 0.3s; }
    @keyframes ipp-piscar {
        0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
        30% { opacity: 1; transform: translateY(-3px); }
    }

    .ipp-input-area {
        display: flex; padding: 14px; background: var(--prev-bg-janela);
        border-top: 1px solid var(--prev-borda); align-items: center; gap: 10px;
    }
    .ipp-input-area input {
        flex-grow: 1; padding: 12px 16px; border-radius: 24px;
        border: 1px solid var(--prev-borda); background: var(--prev-bg-chat);
        color: var(--prev-texto-escuro); outline: none; font-size: 14px;
        transition: border 0.2s, box-shadow 0.2s;
    }
    .ipp-input-area input:focus { border-color: var(--prev-primaria); box-shadow: 0 0 0 3px rgba(10,61,98,0.1); }
    .ipp-enviar {
        background: linear-gradient(135deg, var(--prev-primaria-clara), var(--prev-primaria));
        color: var(--prev-texto-claro); border: none; width: 42px; height: 42px;
        border-radius: 50%; cursor: pointer; display: flex; flex-shrink: 0;
        align-items: center; justify-content: center;
        transition: transform 0.2s ease, background 0.25s ease;
    }
    .ipp-enviar svg { width: 18px; height: 18px; }
    .ipp-enviar:hover { transform: scale(1.06); background: linear-gradient(135deg, var(--prev-destaque-clara), var(--prev-destaque)); }
    .ipp-enviar:disabled { opacity: 0.5; cursor: default; transform: none; }

    @media (max-width: 480px) {
        .ipp-window { right: 12px; left: 12px; width: auto; bottom: 95px; }
        .ipp-history { height: 45vh; }
    }
</style>

<button class="ipp-btn" id="ippBotao" onclick="ippAlternarChat()" aria-label="Abrir chat da IA PrevPeças">
    <svg class="ipp-icon-chat" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H8l-4.7 3.76A.5.5 0 0 1 2.5 20.4V6a2 2 0 0 1 2-2Z"
            stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.08"/>
        <circle cx="8" cy="10.5" r="1.1" fill="currentColor"/>
        <circle cx="12" cy="10.5" r="1.1" fill="currentColor"/>
        <circle cx="16" cy="10.5" r="1.1" fill="currentColor"/>
    </svg>
    <svg class="ipp-icon-close" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
    </svg>
    <span class="ipp-badge"></span>
</button>

<div class="ipp-window" id="ippJanela">
    <div class="ipp-header">
        <div class="ipp-avatar">IA</div>
        <div class="ipp-header-info">
            <div class="ipp-header-title">IA PrevPeças</div>
            <div class="ipp-header-status"><span class="ipp-status-dot"></span> Online agora</div>
        </div>
        <button class="ipp-fechar" onclick="ippAlternarChat()" aria-label="Fechar chat">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    <div class="ipp-history" id="ippHistorico">
        <div class="ipp-linha ia">
            <div class="ipp-mini-avatar">IA</div>
            <div class="ipp-msg ipp-msg-ia">Olá! Sou a IA PrevPeças. O que você procura hoje em estruturas para CFTV?</div>
        </div>
    </div>

    <div class="ipp-digitando" id="ippDigitando">
        <div class="ipp-msg ipp-msg-ia">
            <span class="ipp-ponto"></span><span class="ipp-ponto"></span><span class="ipp-ponto"></span>
        </div>
    </div>

    <div class="ipp-input-area">
        <input type="text" id="ippCampo" placeholder="Digite sua mensagem..." maxlength="2000"
               onkeypress="if(event.key === 'Enter') ippEnviar()">
        <button class="ipp-enviar" id="ippBotaoEnviar" onclick="ippEnviar()" aria-label="Enviar mensagem">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 11.5 20.5 3 13 20.5l-2.4-6.6L3 11.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.12"/>
            </svg>
        </button>
    </div>
</div>

<script>
    function ippAlternarChat() {
        const janela = document.getElementById('ippJanela');
        const botao = document.getElementById('ippBotao');
        const abrindo = !janela.classList.contains('aberta');

        janela.classList.toggle('aberta');
        botao.classList.toggle('aberto');

        if (abrindo) {
            setTimeout(() => document.getElementById('ippCampo').focus(), 260);
        }
    }

    function ippEscaparHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function ippEnviar() {
        const campo = document.getElementById('ippCampo');
        const texto = campo.value.trim();
        if (texto === '') return;

        campo.value = '';
        ippProcessarMensagem(texto);
    }

    function ippProcessarMensagem(pergunta) {
        const historico = document.getElementById('ippHistorico');
        const digitando = document.getElementById('ippDigitando');
        const botaoEnviar = document.getElementById('ippBotaoEnviar');

        historico.insertAdjacentHTML('beforeend', `
            <div class="ipp-linha usuario">
                <div class="ipp-msg ipp-msg-usuario">${ippEscaparHtml(pergunta)}</div>
            </div>
        `);

        digitando.style.display = 'block';
        botaoEnviar.disabled = true;
        historico.scrollTop = historico.scrollHeight;

        const formData = new FormData();
        formData.append('pergunta', pergunta);

        fetch('ia/chat_flutuante.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                const htmlBruto = marked.parse(data.resposta || '');
                const htmlSeguro = DOMPurify.sanitize(htmlBruto, {
                    ALLOWED_TAGS: ['p','br','strong','em','ul','ol','li','h1','h2','h3','h4','h5','h6','code','pre','blockquote','table','thead','tbody','tr','th','td','a','hr'],
                    ALLOWED_ATTR: ['href','title','target','rel']
                });

                historico.insertAdjacentHTML('beforeend', `
                    <div class="ipp-linha ia">
                        <div class="ipp-mini-avatar">IA</div>
                        <div class="ipp-msg ipp-msg-ia">${htmlSeguro}</div>
                    </div>
                `);
            })
            .catch(erro => {
                historico.insertAdjacentHTML('beforeend', `
                    <div class="ipp-linha ia">
                        <div class="ipp-mini-avatar">IA</div>
                        <div class="ipp-msg ipp-msg-ia">Erro ao conectar com o servidor. Verifique sua conexão.</div>
                    </div>
                `);
                console.error(erro);
            })
            .finally(() => {
                digitando.style.display = 'none';
                botaoEnviar.disabled = false;
                historico.scrollTop = historico.scrollHeight;
            });
    }
</script>