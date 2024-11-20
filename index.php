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
    <div class="container border">
        <br>
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
            <h4>Cadastro dos horários de acionamentos automático</h4>
                <!-- Linha 1 -->
                <div class="row">
                    <div class="col-md-3">
                        <form id="scheduleForm">    
                            <div class="form-group">
                                <label for="dayOfWeek">Dia da semana:</label>
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
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="numTomada">Nun. Tomada:</label>
                                <select class="form-control" id="numTomada">
                                    <option value="1">Tomada 1</option>
                                    <option value="2">Tomada 2</option>
                                    <option value="3">Tomada 3</option>
                                    <option value="4">Tomada 4</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="numSchedule">Nun. Agendam:</label>
                                <select class="form-control" id="numSchedule">
                                    <option value="1">Agendamento 1</option>
                                    <option value="2">Agendamento 2</option>
                                    <option value="3">Agendamento 3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="hourOn">Hora de ligar:</label>
                                <input class="form-control" type="number" id="hourOn" min="0" max="23" required>
                            </div>
                        </div>
                    </div>
                    <!-- Linha 2 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="minuteOn">Minuto de ligar:</label>
                                <input class="form-control" type="number" id="minuteOn" min="0" max="59" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="hourOff">Hora de desligar:</label>
                                <input class="form-control" type="number" id="hourOff" min="0" max="23" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="minuteOff">Minuto de desligar:</label>
                                <input class="form-control" type="number" id="minuteOff" min="0" max="59" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button id="cadastrarHorarioBtn" class="btn btn-primary mt-4" type="button">Cadastrar Horário</button>
                        </div>
                    </form>    
                    </div>
            <div class="row">
                <h4>Visualização dos horários de acionamentos automatico</h4>
                <!-- Coluna para a tabela de visualização -->
                <div class="col-md-6">
                    <table id="scheduleTable" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Dia da Semana</th>
                            <th>Tomada</th>
                            <th>Agendamento</th>
                            <th>Hora de Ligar</th>
                            <th>Hora de Desligar</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- Dados do WebSocket serão preenchidos aqui -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <h4 class="mt-5">Mensagens Recebidas</h4>
                <table id="serialPrintsTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Data de Recebimento</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- As mensagens serão inseridas aqui -->
                    </tbody>
                </table>
            </div>

    </div> <!-- Fecha o container -->  
    
   
    <script>
        $(document).ready(function() {
            // Estabelece a conexão WebSocket com o servidor
            var ws = new WebSocket("ws://localhost:8080");
            
            // Função para atualizar a tabela de mensagens recebidas
            function atualizarTabelaSerialPrints(message) {
                // Obtém a referência da tabela
                const tabela = document.getElementById('serialPrintsTable').getElementsByTagName('tbody')[0];

                // Cria uma nova linha na tabela
                const novaLinha = tabela.insertRow(0); // Insere no início da tabela

                // Adiciona a coluna de data e hora
                const colunaData = novaLinha.insertCell(0);
                const agora = new Date();
                colunaData.textContent = agora.toLocaleString();

                // Adiciona a coluna de mensagem
                const colunaMensagem = novaLinha.insertCell(1);
                colunaMensagem.textContent = message;
            }

            // Define o comportamento do botão 'Cadastrar Horário' quando clicado
            $('#cadastrarHorarioBtn').click(function() {
                // Obtém os valores dos campos do formulário
                var diaSemana = $('#dayOfWeek').val();
                var numeroTomada = $('#numTomada').val();
                var numeroAgendamento = $('#numSchedule').val();
                var horaLigar = $('#hourOn').val();               
                var minutoLigar = $('#minuteOn').val();
                var horaDesligar = $('#hourOff').val();
                var minutoDesligar = $('#minuteOff').val();

                // Constrói a mensagem a ser enviada ao servidor
                var mensagem = `${diaSemana},${numeroTomada},${numeroAgendamento},${horaLigar.padStart(2, '0')},${minutoLigar.padStart(2, '0')},${horaDesligar.padStart(2, '0')},${minutoDesligar.padStart(2, '0')}`;
                
                // Verifica se a conexão WebSocket está aberta e envia a mensagem
                if (ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({topic: "silvanojose.tcc/schedule", message: mensagem}));
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
                try {
                    const message = JSON.parse(event.data);
                    
                    if (message.topic === 'silvanojose.tcc/serialprints') {
                        atualizarTabelaSerialPrints(message.message);
                    } else if (message.topic || message.topico) {
                        // Verifica se é um tópico de temperatura e atualiza
                        if (isTemperaturaTopic(message.topic || message.topico)) {
                            atualizarTemperatura(message);
                        } else {
                            atualizarEstadoDaTomada(message);
                        }
                    } else if (message.tipo === 'schedule') {
                        console.log("Pagina recebeu mensagem do tipo schedule e vai chamar atualizarTabela:", message);
                        // Verifica se message.message está definido e é um objeto JSON
                        if (typeof message.message === 'object' && message.message !== null) {
                            atualizarTabelaSchedule(message.message);
                        } else {
                            console.error("Dados recebidos não são uma string JSON válida:", message.message);
                        }
                    }
                } catch (error) {
                    console.error("Erro ao processar mensagem WebSocket:", error);
                }
            };

            function atualizarTabelaSchedule(schedule) {
                // Limpa a tabela antes de adicionar novas linhas
                const tabela = document.getElementById('scheduleTable');
                const tbody = tabela.querySelector('tbody');
                tbody.innerHTML = ''; // Limpa as linhas anteriores

                if (typeof schedule !== 'object' || schedule === null) {
                    console.error("Formato de dados inválido:", schedule);
                    return;
                }

                // Mapeamento para ordenar os dias da semana
                const diasDaSemana = ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"];

                // Transformar o cronograma em um array
                const scheduleArray = Object.entries(schedule).flatMap(([dia, infoDia]) => {
                    if (typeof infoDia === 'object' && !Array.isArray(infoDia)) {
                        return Object.entries(infoDia).flatMap(([tomada, agendamentos]) => {
                            if (typeof agendamentos === 'object' && !Array.isArray(agendamentos)) {
                                return Object.entries(agendamentos).map(([agendamento, horarios]) => {
                                    return {
                                        diaSemana: diasDaSemana.indexOf(dia),
                                        tomada: parseInt(tomada.replace('tomada', ''), 10),
                                        agendamento: parseInt(agendamento.replace('agendamento', ''), 10),
                                        horaLigada: horarios.horaLigada,
                                        minutoLigada: horarios.minutoLigada,
                                        horaDesligada: horarios.horaDesligada,
                                        minutoDesligada: horarios.minutoDesligada
                                    };
                                });
                            }
                            return [];
                        });
                    }
                    return [];
                });

                // Ordenar os horários pelos dias da semana e pelas tomadas
                scheduleArray.sort((a, b) => {
                    if (a.diaSemana !== b.diaSemana) {
                        return a.diaSemana - b.diaSemana;
                    } else if (a.tomada !== b.tomada) {
                        return a.tomada - b.tomada;
                    } else {
                        return a.agendamento - b.agendamento;
                    }
                });

                // Adiciona as linhas da tabela com as informações ordenadas
                scheduleArray.forEach(horario => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${diasDaSemana[horario.diaSemana]}</td>
                        <td>${horario.tomada}</td>
                        <td>${horario.agendamento}</td>
                        <td>${String(horario.horaLigada).padStart(2, '0')}:${String(horario.minutoLigada).padStart(2, '0')}</td>
                        <td>${String(horario.horaDesligada).padStart(2, '0')}:${String(horario.minutoDesligada).padStart(2, '0')}</td>
                    `;

                    // Adiciona um evento de clique à linha da tabela para preencher o formulário
                    row.addEventListener('click', () => {
                        const dayOptions = document.getElementById('dayOfWeek').options;
                        const numericDayOfWeek = horario.diaSemana;
                        const selectedIndex = Array.from(dayOptions).findIndex(option => parseInt(option.value) === numericDayOfWeek);
                        if (selectedIndex !== -1) {
                            document.getElementById('dayOfWeek').selectedIndex = selectedIndex;
                        }
                        document.getElementById('hourOn').value = horario.horaLigada;
                        document.getElementById('minuteOn').value = horario.minutoLigada;
                        document.getElementById('hourOff').value = horario.horaDesligada;
                        document.getElementById('minuteOff').value = horario.minutoDesligada;
                        document.getElementById('numTomada').value = horario.tomada;
                        document.getElementById('numSchedule').value = horario.agendamento;
                    });
                    tbody.appendChild(row);
                });
            }


            // Função para verificar se um tópico é de temperatura
            function isTemperaturaTopic(topic) {
                return topic.startsWith('silvanojose.tcc/temperatura');
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
                    ws.send(JSON.stringify({topic: "silvanojose.tcc/tomada" + numeroTomada, message: estado}));
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
                    var topico = "silvanojose.tcc/tomada" + i;
                    console.log("Enviando solicitação para obter estado inicial...");
                    console.log({tipo: "getEstado", topico: topico});
                    ws.send(JSON.stringify({tipo: "getEstado", topico: topico}));
                }
            }

            // Função para carregar as temperaturas iniciais dos boxes ao se conectar
            function carregarTemperIniciais() {
                console.log("Entrou carregarTemperIniciais");
                const topicosTemperatura = [
                    'silvanojose.tcc/temperaturaBoxTomadas',
                    'silvanojose.tcc/temperaturaTomada1',
                    'silvanojose.tcc/temperaturaTomada2',
                    'silvanojose.tcc/temperaturaTomada3',
                    'silvanojose.tcc/temperaturaTomada4'
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
