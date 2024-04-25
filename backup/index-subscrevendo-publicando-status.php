<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Controle da Tomada</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #botaoTomada {
            padding: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <button id="botaoTomada">Ligar/Deslig-Tomada 1</button>
    <label id="statusTomada">Desconectado</label>

    <script>
        $(document).ready(function() {
            var ws = new WebSocket("ws://192.168.0.107:8080");

            ws.onopen = function() {
                console.log("Conexão WebSocket aberta.");
                carregarEstadoInicial();
            };

            ws.onmessage = function(event) {
                var message = JSON.parse(event.data);
                console.log("Resposta do servidor com o status atual, variavel message:", message);
                console.log("Resposta do servidor com variavel message.topic:", message.topic);
                console.log("Resposta do servidor com variavel message.topico:", message.topico);
                console.log("Resposta do servidor com variavel message,message:", message.message);
                // Atualiza o estado da tomada quando uma mensagem é recebida
                if ((message.topic || message.topico) === "silvanojose/tomada1") {
                    atualizarEstadoDaTomada(message.message);
                }
            };

            // Função para enviar mensagem MQTT quando o botão é clicado
            $("#botaoTomada").click(function() {
                if (ws.readyState === WebSocket.OPEN) {
                    var estado = $("#statusTomada").text() === "Ligado" ? "0" : "1";
                    console.log("Enviando mensagem para o tópico silvanojose/tomada1:", estado);
                    ws.send(JSON.stringify({topic: "silvanojose/tomada1", message: estado}));
                }
            });

            ws.onerror = function(error) {
                console.log("Erro na conexão WebSocket.", error);
            };

            ws.onclose = function() {
                console.log("Conexão WebSocket fechada.");
            };

            function carregarEstadoInicial() {
                if (ws.readyState === WebSocket.OPEN) {
                    // Solicita o estado atual ao conectar
                    console.log("Enviando solicitação para obter estado inicial...");
                    ws.send(JSON.stringify({tipo: "getEstado", topico: "silvanojose/tomada1"}));
                } else {
                    console.log("A conexão WebSocket não está aberta para enviar a solicitação.");
                }
            }

            function atualizarEstadoDaTomada(estado) {
                const statusTomada = document.getElementById('statusTomada');
                const botaoTomada = document.getElementById('botaoTomada');
                console.log("Dentro da função atualizarEstadoDaTomada, vai atualizar botão");
                console.log(estado);

                if (estado === '1') {
                    statusTomada.textContent = 'Ligado';
                    botaoTomada.style.backgroundColor = 'green';
                } else if (estado === '0') {
                    statusTomada.textContent = 'Desligado';
                    botaoTomada.style.backgroundColor = 'red';
                } else {
                    statusTomada.textContent = 'Desconectado';
                    botaoTomada.style.removeProperty('background-color');
                }
            }  


        });



    </script>
</body>
</html>