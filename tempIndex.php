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
                <h4>Cadastro e visualização dos horários de acionamentos automático</h4>
                <!-- Coluna para o formulário de cadastro -->
                <div class="col-md-6">
                <form id="scheduleForm">
                    <div class="form-row">
                        <div class="form-group col-md-6">
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
                        <div class="form-group col-md-6">
                            <label for="hourOn">Hora de ligar:</label>
                            <input class="form-control" type="number" id="hourOn" min="0" max="23" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="minuteOn">Minuto de ligar:</label>
                            <input class="form-control" type="number" id="minuteOn" min="0" max="59" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="hourOff">Hora de desligar:</label>
                            <input class="form-control" type="number" id="hourOff" min="0" max="23" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="minuteOff">Minuto de desligar:</label>
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
                            <th>Tomada</th>
                            <th>Agendamento</th>
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
            var ws = new WebSocket("ws://192.168.0.118:8080");

            // Define o comportamento do botão 'Cadastrar Horário' quando clicado
            $('#cadastrarHorarioBtn').click(function() {
                // Obtém os valores dos campos do formulário
                var diaSemana = $('#dayOfWeek').val();
                var horaLigar = $('#hourOn').val().padStart(2, '0');
                var minutoLigar = $('#minuteOn').val().padStart(2, '0');
                var horaDesligar = $('#hourOff').val().padStart(2, '0');
                var minutoDesligar = $('#minuteOff').val().padStart(2, '0');

                // Constrói a mensagem a ser enviada ao servidor
                var mensagem = {
                    diaSemana: diaSemana,
                    horaLigar: horaLigar,
                    minutoLigar: minutoLigar,
                    horaDesligar: horaDesligar,
                    minutoDesligar: minutoDesligar
                };
                
                // Verifica se a conexão WebSocket está aberta e envia a mensagem
                if (ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({tipo: 'schedule', message: mensagem}));
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
                console.log("Conexão WebSocket estabelecida.");
                carregarEstadosIniciais(); // Carrega o estado inicial das tomadas
                carregarTemperIniciais(); // Carrega as temperaturas iniciais
                ws.send(JSON.stringify({ tipo: 'getSchedule' }));
                console.log("Passou pela solicitação de getSchedule");
            };

            // Função executada quando uma mensagem é recebida do servidor
            ws.onmessage = function(event) {
                var mensagem = JSON.parse(event.data);
                console.log("Mensagem recebida:", mensagem);

                if (mensagem.tipo === 'status') {
                    console.log("Estado da tomada recebido:", mensagem);
                    atualizarEstadoTomada(mensagem.tomada, mensagem.estado);

                } else if (mensagem.tipo === 'temperatura') {
                    atualizarTemperatura(mensagem.tomada, mensagem.temperatura);

                } else if (mensagem.tipo === 'schedule') {
                    var dadosHorarios = mensagem.message;
                    console.log("Dados de horário recebidos:", dadosHorarios);
                    atualizarTabelaHorarios(dadosHorarios);

                } else {
                    console.error("Mensagem de tipo inválido ou dados ausentes:", mensagem);
                }
            };

            function atualizarTabelaHorarios(dados) {
                var diasDaSemana = ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"];
                var tabela = $('#scheduleTable tbody');
                tabela.empty();

                // Verifica se os dados estão no formato esperado
                if (typeof dados === 'object' && !Array.isArray(dados)) {
                    $.each(dados, function(dia, infoDia) {
                        if (typeof infoDia === 'object' && !Array.isArray(infoDia)) {
                            var diaNome = diasDaSemana[dia];
                            $.each(infoDia, function(tomada, agendamentos) {
                                if (typeof agendamentos === 'object' && !Array.isArray(agendamentos)) {
                                    $.each(agendamentos, function(agendamento, horarios) {
                                        if (typeof horarios === 'object' && !Array.isArray(horarios)) {
                                            var linha = $('<tr>');
                                            linha.append($('<td>').text(diaNome));
                                            linha.append($('<td>').text(tomada));
                                            linha.append($('<td>').text(agendamento));
                                            linha.append($('<td>').text(horarios.horaLigada + ':' + horarios.minutoLigada));
                                            linha.append($('<td>').text(horarios.horaDesligada + ':' + horarios.minutoDesligada));
                                            tabela.append(linha);
                                        }
                                    });
                                }
                            });
                        }
                    });
                } else {
                    console.error("Formato dos dados de horários é inválido:", dados);
                }
            }


            function carregarEstadosIniciais() {
                for (var i = 1; i <= 4; i++) {
                    $('#statusTomada' + i).text("Desconectado");
                }
            }

            function carregarTemperIniciais() {
                for (var i = 1; i <= 4; i++) {
                    $('#temperaturaTomada' + i).text("-- °C");
                }
                $('#temperaturaBoxTomadas').text("-- °C");
            }

            function atualizarEstadoTomada(tomada, estado) {
                $('#statusTomada' + tomada).text(estado ? "Conectado" : "Desconectado");
            }

            function atualizarTemperatura(tomada, temperatura) {
                $('#temperaturaTomada' + tomada).text(temperatura + " °C");
                // Atualizar a temperatura do box de tomadas se a mensagem contiver essa informação
                if (tomada === 'BoxTomadas') {
                    $('#temperaturaBoxTomadas').text(temperatura + " °C");
                }
            }

        });
    </script>
</body>
</html>
