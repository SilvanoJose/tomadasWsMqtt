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
    <button id="botaoTomada">Ligado/Desligado</button>
    <label id="statusTomada">Desconectado</label>

    <script>
        $(document).ready(function() {
            var ws = new WebSocket("ws://192.168.0.112:8080");

            ws.onopen = function() {
                console.log("Conexão WebSocket aberta.");
                // Solicita o estado atual ao conectar
                ws.send(JSON.stringify({tipo: "getEstado", topico: "silvanojose/tomada1"}));
            };

            ws.onmessage = function(event) {
                var message = JSON.parse(event.data);
                console.log(message.topic); // Acessa a propriedade correta "topic"
                
                // Verifica se o tópico é "silvanojose/tomada1"
                if (message.topic === "silvanojose/tomada1") {
                    var estado = message.message; // Acessa a propriedade correta "message"
                    if (estado === "1") {
                        console.log("Recebeu 1 no tópico.");
                        $("#statusTomada").text("Ligado");
                        $("#botaoTomada").css("background-color", "green");
                    } else if (estado === "0") {
                        console.log("Recebeu 0 no tópico.");
                        $("#statusTomada").text("Desligado");
                        $("#botaoTomada").css("background-color", "red");
                    }
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

            // Solicita o estado atual do tópico "silvanojose/tomada1" ao abrir a página
            //ws.onopen(); // Chama manualmente para solicitar o estado ao abrir a página

            ws.onerror = function(error) {
                console.log("Erro na conexão WebSocket.", error);
            };

            ws.onclose = function() {
                console.log("Conexão WebSocket fechada.");
            };
        });

    </script>
</body>
</html>