<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Controle da Tomada</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .botaoTomada {
            padding: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <br/>
        <h2 class="bg-info text-center">Sistema Controle de Tomadas V-4.0 <h2>   
        <h4>Acionadores tomadas</h4>
        <div class="row">
            <div class="col-3">   
                <button class="botaoTomada" id="botaoTomada1">Ligar/Deslig-Tomada 1</button>
                <label id="statusTomada1">Desconectado</label>
            </div>
            <div class="col-3">   
                <button class="botaoTomada" id="botaoTomada2">Ligar/Deslig-Tomada 2</button>
                <label id="statusTomada2">Desconectado</label>
            </div>
            <div class="col-3">   
                <button class="botaoTomada" id="botaoTomada3">Ligar/Deslig-Tomada 3</button>
                <label id="statusTomada3">Desconectado</label>
            </div>
            <div class="col-3">   
                <button class="botaoTomada" id="botaoTomada4">Ligar/Deslig-Tomada 4</button>
                <label id="statusTomada4">Desconectado</label>
            </div>
        </div> 
        
        <h4 class="mt-5">Visualizadores de temperaturas</h4>    
        <div class="row">
            <div class="col-4">  

            </div>
            <div class="col-2">

            </div>
            <div class="col-2">

            </div>
            <div class="col-2">

            </div>
            <div class="col-2">
                   
            </div>       
        </div>
    </div> <!-- Fecha o container -->  
    
    <script>
        $(document).ready(function() {
            var ws = new WebSocket("ws://192.168.0.101:8080");

            ws.onopen = function() {
                console.log("Conexão WebSocket aberta.");
                carregarEstadosIniciais();
            };

            ws.onmessage = function(event) {
                var message = JSON.parse(event.data);
                if (message.topic || message.topico) {
                    console.log("Dentro do if que vai chamar atualizarEstadoDaTomada, variavel message:", message);
                    atualizarEstadoDaTomada(message);
                }
            };

            // Função para enviar mensagem MQTT quando o botão é clicado
            $("[id^='botaoTomada']").click(function() {
                var numeroTomada = this.id.replace('botaoTomada', '');
                var estadoTomada = $("#statusTomada" + numeroTomada).text();
                var estado = estadoTomada === "Ligado" ? "0" : "1";
                if (ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({topic: "silvanojose/tomada" + numeroTomada, message: estado}));
                }
            });

            ws.onerror = function(error) {
                console.log("Erro na conexão WebSocket.", error);
            };

            ws.onclose = function() {
                console.log("Conexão WebSocket fechada.");
            };

            // Função para carregar os estados iniciais de todas as tomadas ao se conectar
            function carregarEstadosIniciais() {
                for (var i = 1; i <= 4; i++) {
                    var topico = "silvanojose/tomada" + i;
                    console.log("Enviando solicitação para obter estado inicial...");
                    console.log({tipo: "getEstado", topico: topico});
                    ws.send(JSON.stringify({tipo: "getEstado", topico: topico}));
                }
            }

            function atualizarEstadoDaTomada(message) {
                console.log("Dentro do atualizarEstadoDaTomada, variavel message:", message);
                if (message && (message.topic || message.topico)) {
                    console.log("Dentro do atualizarEstadoDaTomada, passou pelo if message & ....");
                    var numeroTomada = message.topic ? message.topic.substring(message.topic.length - 1) : message.topico.substring(message.topico.length - 1); // Obtém o número da tomada
                    var statusTomada = $("#statusTomada" + numeroTomada);
                    var botaoTomada = $("#botaoTomada" + numeroTomada);
                    if (message.message === '1') {
                        console.log("Dentro do atualizarEstadoDaTomada, passou pelo if message  1 ....");
                        statusTomada.text('Ligado');
                        botaoTomada.css('background-color', 'green');
                    } else if (message.message === '0') {
                        console.log("Dentro do atualizarEstadoDaTomada, passou pelo if message  0 ....");
                        statusTomada.text('Desligado');
                        botaoTomada.css('background-color', 'red');
                    } else {
                        console.log("Dentro do atualizarEstadoDaTomada, caiu no else saindo fora sem atualizar ....");
                        statusTomada.text('Desconectado');
                        botaoTomada.css('background-color', '');
                    }
                } else {
                    console.error("Mensagem inválida recebida:", message);
                }
            }
        });

    </script>

    <!-- jQuery, Popper.js, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
