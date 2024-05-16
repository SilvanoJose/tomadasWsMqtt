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
                        <label class="status-label">Temperat Box Tomadas:</label>
                    </td>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura Tomada 1:</label>
                    </td>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura Tomada 2:</label>
                    </td>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura Tomada 3:</label>
                    </td>
                    <td class="col-md-2">
                        <label class="status-label">Temperatura Tomada 4:</label>
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
            <div class="row">
                <h4>Cadastro e visualização dos horários de acionamentos automatico</h4>
                <!-- Coluna para o formulário de cadastro -->
                <div class="col-md-6">
                <form id="scheduleForm">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label para="dayOfWeek">Dia da semana:</label>
                            <select class="form-control" id="dayOfWeek">
                                <option value="0">Domingo</option>
                                <option value="1">Segunda-feira</option>
                                <option value="2">Terça-feira</option>
                                <option value="3">Quarta-feira</option>
                                <option value="4">Quinta-feira</option>
                                <option value="5">Sexta-feira</option>
                                <option value="6">Sábado</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label para="hourOn">Hora de ligar:</label>
                            <input class="form-control" type="number" id="hourOn" min="0" max="23" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label para="minuteOn">Minuto de ligar:</label>
                            <input class="form-control" type="number" id="minuteOn" min="0" max="59" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label para="hourOff">Hora de desligar:</label>
                            <input class="form-control" type="number" id="hourOff" min="0" max="23" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label para="minuteOff">Minuto de desligar:</label>
                            <input class="form-control" type="number" id="minuteOff" min="0" max="59" required>
                        </div>
                    </div>
                    <button id="cadastrarHorarioBtn" class="btn btn-primary" type="button">Cadastrar Horário</button>
                </form>
            </div>

                <!-- Coluna para a tabela de visualização -->
                <div class="col-md-6">
                    <table id="scheduleTable" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Dia da Semana</th>
                            <th>Hora de Ligação</th>
                            <th>Hora de Desligamento</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- Dados do WebSocket serão preenchidos aqui -->
                        </tbody>
                    </table>
                </div>
            </div>

    </div> <!-- Fecha o container -->  
    
   
    <script>
        $(document).ready(function() {
            // Estabelece a conexão WebSocket com o servidor
            var ws = new WebSocket("ws://192.168.0.113:8080");

            // Define o comportamento do botão 'Cadastrar Horário' quando clicado
            $('#cadastrarHorarioBtn').click(function() {
                // Obtém os valores dos campos do formulário
                var diaSemana = $('#dayOfWeek').val();
                var horaLigar = $('#hourOn').val();
                var minutoLigar = $('#minuteOn').val();
                var horaDesligar = $('#hourOff').val();
                var minutoDesligar = $('#minuteOff').val();

                // Constrói a mensagem a ser enviada ao servidor
                var mensagem = `${diaSemana},${horaLigar.padStart(2, '0')},${minutoLigar.padStart(2, '0')},${horaDesligar.padStart(2, '0')},${minutoDesligar.padStart(2, '0')}`;
                
                // Verifica se a conexão WebSocket está aberta e envia a mensagem
                if (ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({topic: "silvanojose/schedule", message: mensagem}));
                }

                // Limpa os campos do formulário após o clique em salvar
                $('#scheduleForm')[0].reset();

                // Recarrega a tabela para refletir as alterações
                setTimeout(() => {
                    ws.send(JSON.stringify({ tipo: 'getSchedule' }));
                    console.log("Solicitação de getSchedule enviada.");
                }, 500); // Uma pequena espera para garantir que os dados no servidor sejam atualizados.
            });

            // Função executada quando a conexão WebSocket é aberta
            ws.onopen = function() {
                console.log("Conexão WebSocket aberta.");
                // Carrega os estados iniciais das tomadas ao se conectar
                carregarEstadosIniciais();
                console.log("Passou pelo carregarEstadosIniciais");
                // Carrega as temperaturas iniciais dos boxes ao se conectar
                carregarTemperIniciais();
                console.log("Passou pelo carregarTemperIniciais");
                // Solicita os dados do cronograma ao servidor
                ws.send(JSON.stringify({ tipo: 'getSchedule' }));
            };

            // Função executada quando uma mensagem é recebida do servidor
            ws.onmessage = function(event) {
                var message = JSON.parse(event.data);
                const data = JSON.parse(event.data);

                if (message.topic || message.topico) {
                    // Verifica se é um tópico de temperatura e atualiza
                    if (isTemperaturaTopic(message.topic || message.topico)) {
                        atualizarTemperatura(message);
                    } else {
                        atualizarEstadoDaTomada(message);
                    }
                } else if (data.tipo === 'schedule') {
                    console.log("Dados do cronograma recebidos:", data);
                    // Atualiza a tabela com os dados mais recentes
                    atualizarTabelaSchedule(data.data);
                }
            };

            function atualizarTabelaSchedule(data) {
                // Limpa a tabela antes de adicionar novas linhas
                const tabela = document.getElementById('scheduleTable');
                const tbody = tabela.querySelector('tbody');
                tbody.innerHTML = ''; // Limpa as linhas anteriores

                // Adiciona as linhas da tabela com as informações recebidas
                const schedule = JSON.parse(data);

                // Mapeamento para ordenar os dias da semana
                const dayOrder = {
                    'Domingo': 0,
                    'Segunda': 1,
                    'Terça': 2,
                    'Quarta': 3,
                    'Quinta': 4,
                    'Sexta': 5,
                    'Sábado': 6
                };

                // Transformar o cronograma em uma array e ordenar pelos dias da semana
                const sortedSchedule = Object.entries(schedule).sort(
                    (a, b) => dayOrder[a[0]] - dayOrder[b[0]]
                );

                // Adiciona as linhas da tabela com as informações ordenadas
                sortedSchedule.forEach(([dia, horario]) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${dia}</td>
                        <td>${horario.horaLigada}:${horario.minutoLigada}</td>
                        <td>${horario.horaDesligada}:${horario.minutoDesligada}</td>
                    `;

                    // Mapeia o nome do dia da semana para um número
                    const dayNameToNumber = {
                        "Domingo": 0,
                        "Segunda": 1,
                        "Terça": 2,
                        "Quarta": 3,
                        "Quinta": 4,
                        "Sexta": 5,
                        "Sábado": 6
                    };

                    // Adiciona um evento de clique à linha da tabela para preencher o formulário
                    row.addEventListener('click', () => {
                        const dayOptions = document.getElementById('dayOfWeek').options;
                        const dayOfWeekClicked = dia;
                        const numericDayOfWeek = dayNameToNumber[dayOfWeekClicked];
                        const selectedIndex = Array.from(dayOptions).findIndex(option => parseInt(option.value) === numericDayOfWeek);
                        if (selectedIndex !== -1) {
                            document.getElementById('dayOfWeek').selectedIndex = selectedIndex;
                        }
                        document.getElementById('hourOn').value = horario.horaLigada;
                        document.getElementById('minuteOn').value = horario.minutoLigada;
                        document.getElementById('hourOff').value = horario.horaDesligada;
                        document.getElementById('minuteOff').value = horario.minutoDesligada;
                    });
                    tbody.appendChild(row);
                });
            }

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
                $('#' + labelId).text(temperatura + ' °C');
            }

            // Função para enviar mensagem MQTT quando o botão é clicado
            $("[id^='botaoTomada']").click(function() {
                var numeroTomada = this.id.replace('botaoTomada', '');
                var estadoTomada = $("#statusTomada" + numeroTomada).text();
                var estado = estadoTomada === "Ligado" ? "0" : "1";
                if (ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({topic: "silvanojose/tomada" + numeroTomada, message: estado}));
                }
            });

            // Função executada em caso de erro na conexão WebSocket
            ws.onerror = function(error) {
                console.log("Erro na conexão WebSocket.", error);
            };

            // Função executada quando a conexão WebSocket é fechada
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

                topicosTemperatura.forEach((topico) => {
                    console.log("Enviando solicitação para obter temperatura inicial...");
                    console.log({ tipo: "getTemperatura", topico: topico });
                    ws.send(JSON.stringify({ tipo: "getTemperatura", topico: topico }));
                });
            }

            // Função para atualizar o estado da tomada
            function atualizarEstadoDaTomada(message) {
                console.log("Dentro do atualizarEstadoDaTomada, variavel message:", message);
                if (message && (message.topic || message.topico)) {
                    console.log("Dentro do atualizarEstadoDaTomada, passou pelo if message & ....");
                    var numeroTomada = message.topic ? message.topic.substring(message.topic.length - 1) : message.topico.substring(message.topico.length - 1); // Obtém o número da tomada
                    var statusTomada = $("#statusTomada" + numeroTomada);
                    var botaoTomada = $("#botaoTomada" + numeroTomada);
                    if (message.message === '1') {
                        console.log("Dentro do atualizarEstadoDaTomada, passou pelo if message 1 ....");
                        statusTomada.text('Ligado');
                        botaoTomada.css('background-color', 'green');
                    } else if (message.message === '0') {
                        console.log("Dentro do atualizarEstadoDaTomada, passou pelo if message 0 ....");
                        statusTomada.text('Deslig');
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

            // Função para carregar os horários do servidor para um dia da semana
            function carregarHorarios(diaSemana) {
                fetch(`/getHorarios?diaSemana=${diaSemana}`)
                    .then((response) => response.json())
                    .then((data) => {
                        // Preenche os campos com os dados recebidos
                        document.getElementById('hourOn').value = data.horaLigada;
                        document.getElementById('minuteOn').value = data.minutoLigada;
                        document.getElementById('hourOff').value = data.horaDesligada;
                        document.getElementById('minuteOff').value = data.minutoDesligada;
                    })
                    .catch((error) => {
                        console.error("Erro ao carregar horários:", error);
                    });
            }

            // Adiciona um evento de clique para cada opção de dia da semana
            document.getElementById('dayOfWeek').addEventListener('change', function () {
                const diaSemana = parseInt(this.value);
                carregarHorarios(diaSemana);
            });

        });
    </script>

    <!-- jQuery, Popper.js, Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
