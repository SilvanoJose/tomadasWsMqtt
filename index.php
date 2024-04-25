<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Controle da Tomada</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .botaoTomada {
            padding: 10px;
            font-size: 16px;
        }
        .container {
            margin-top: 30px;
        }
        .status-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="bg-info text-center p-3 mb-4">Sistema de Controle de Tomadas V-4.0</h2>
        <h4>Acionadores de Tomadas</h4>
        <div class="row">
            <div class="col-md-3">   
                <button class="btn btn-primary botaoTomada" id="botaoTomada1">Ligar/Desligar Tomada 1</button>
                <label id="statusTomada1" class="status-label">Desconectado</label>
            </div>
            <div class="col-md-3">   
                <button class="btn btn-primary botaoTomada" id="botaoTomada2">Ligar/Desligar Tomada 2</button>
                <label id="statusTomada2" class="status-label">Desconectado</label>
            </div>
            <div class="col-md-3">   
                <button class="btn btn-primary botaoTomada" id="botaoTomada3">Ligar/Desligar Tomada 3</button>
                <label id="statusTomada3" class="status-label">Desconectado</label>
            </div>
            <div class="col-md-3">   
                <button class="btn btn-primary botaoTomada" id="botaoTomada4">Ligar/Desligar Tomada 4</button>
                <label id="statusTomada4" class="status-label">Desconectado</label>
            </div>
        </div> 
        
        <h4 class="mt-5">Visualizadores de Temperaturas</h4>    
            <table class="table table-bordered">
                <tr>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura do Box das Tomadas:</label>
                    </td>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura da Tomada 1:</label>
                    </td>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura da Tomada 2:</label>
                    </td>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura da Tomada 3:</label>
                    </td>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura da Tomada 4:</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span id="temperaturaBoxTomadas">-- °C</span>
                    </td>
                    <td>
                        <span id="temperaturaTomada1">-- °C</span>
                    </td>
                    <td>
                        <span id="temperaturaTomada2">-- °C</span>
                    </td>
                    <td>
                        <span id="temperaturaTomada3">-- °C</span>
                    </td>
                    <td>
                        <span id="temperaturaTomada4">-- °C</span>
                    </td>
                </tr>
            </table>

<h4 class="mt-5">Cadastramento dos horários de acionamento automático</h4>
<div class="row justify-content-center">
    <form id="scheduleForm" class="col-md-12">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="dayOfWeek">Dia da semana:</label>
                <select class="form-control" id="dayOfWeek" name="dayOfWeek">
                    <option value="0">Domingo</option>
                    <option value="1">Segunda-feira</option>
                    <option value="2">Terça-feira</option>
                    <option value="3">Quarta-feira</option>
                    <option value="4">Quinta-feira</option>
                    <option value="5">Sexta-feira</option>
                    <option value="6">Sábado</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="hourOn">Hora de ligar:</label>
                <div class="row">
                    <div class="col">
                        <input class="form-control" type="number" id="hourOn" name="hourOn" min="0" max="23" required>
                    </div>
                    <div class="col">
                        <input class="form-control" type="number" id="minuteOn" name="minuteOn" min="0" max="59" required>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-4">
                <label for="hourOff">Hora de desligar:</label>
                <div class="row">
                    <div class="col">
                        <input class="form-control" type="number" id="hourOff" name="hourOff" min="0" max="23" required>
                    </div>
                    <div class="col">
                        <input class="form-control" type="number" id="minuteOff" name="minuteOff" min="0" max="59" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group col-md-12">
            <button id="cadastrarHorarioBtn" class="btn btn-primary" type="button">Cadastrar Horário</button>
        </div>
    </form>
</div>


    </div> <!-- Fecha o container -->  
    
   
    <script>
        $(document).ready(function() {

            var ws = new WebSocket("ws://192.168.0.109:8080");

            $('#cadastrarHorarioBtn').click(function() {
                // Captura os valores dos campos do formulário
                console.log("Clicou em cadastrarHorarioBtn");
                var diaSemana = $('#dayOfWeek').val();
                var horaLigar = $('#hourOn').val();
                var minutoLigar = $('#minuteOn').val();
                var horaDesligar = $('#hourOff').val();
                var minutoDesligar = $('#minuteOff').val();

                // Formata a mensagem no formato desejado
                var mensagem = diaSemana + ',' + horaLigar.padStart(2, '0') + ',' + minutoLigar.padStart(2, '0') + ',' + horaDesligar.padStart(2, '0') + ',' + minutoDesligar.padStart(2, '0');
                console.log("Conteudo enviado pelo clique: ", mensagem);
                // Envia a mensagem para o servidor via WebSocket
                if (ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({topic: "silvanojose/schedule", message: mensagem}));
                } else {
                    console.error("Erro: conexão WebSocket não está aberta.");
                }
            });


            ws.onopen = function() {
                console.log("Conexão WebSocket aberta.");
                carregarEstadosIniciais();
                console.log("Passou pelo carregarEstadosIniciais");
                carregarTemperIniciais();
                console.log("Passou pelo carregarTemperIniciais");
            };

            ws.onmessage = function(event) {
                var message = JSON.parse(event.data);
                if (message.topic || message.topico) {
                    // Verifica se é um tópico de temperatura
                    if (isTemperaturaTopic(message.topic || message.topico)) {
                        atualizarTemperatura(message);
                    } else {
                        atualizarEstadoDaTomada(message);
                    }
                }
            };

            // Função para verificar se um tópico é de temperatura
            function isTemperaturaTopic(topic) {
                return topic.startsWith('silvanojose/temperatura');
            }

            // Função para atualizar os valores de temperatura
            function atualizarTemperatura(message) {
                console.log("Retornou do server e está no atualizarTemperatura", message);
                var temperatura = message.message;
                var topic = message.topic || message.topico;
                var labelId = topic.split('/').pop(); // Obtém o último elemento após a última barra
//                $('#' + labelId).text(topic + ': ' + temperatura + ' °C');
                $('#' + labelId).text( temperatura + ' °C');

            }

            // Função para enviar mensagem MQTT quando o botão é clicado
            $("[id^='botaoTomada']").click(function() {
                var numeroTomada = this.id.replace('botaoTomada', '');
                var estadoTomada = $("#statusTomada" + numeroTomada).text();
                var estado = estadoTomada === "Ligado" ? "0" : "1"
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

            // Função para carregar as temperaturas iniciais dos boxes ao se conectar
            function carregarTemperIniciais() {
                console.log("Entrou carregarTemperIniciais");
                const topicosTemperatura = [
                    'silvanojose/temperaturaBoxTomadas',
                    'silvanojose/temperaturaTomada1',
                    'silvanojose/temperaturaTomada2',
                    'silvanojose/temperaturaTomada3',
                    'silvanojose/temperaturaTomada4'
                ];

                topicosTemperatura.forEach((topico, indice) => {
                    console.log("Enviando solicitação para obter temperatura inicial...");
                    console.log({ tipo: "getTemperatura", topico: topico });
                    ws.send(JSON.stringify({ tipo: "getTemperatura", topico: topico }));
                });
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
