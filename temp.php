<? php 
Parece que pode haver alguns problemas na forma como a mensagem está sendo recebida e a tabela está sendo atualizada. Vamos garantir que a solicitação para obter o cronograma atualizado seja feita corretamente e que os dados sejam processados e exibidos na tabela.

Vamos fazer algumas adaptações e melhorias ao código para garantir que, após enviar a nova programação, a tabela seja devidamente atualizada:

1. *Certifique-se de solicitar a atualização da tabela após enviar a programação*
2. *Garanta que a estrutura da mensagem seja correta tanto no envio quanto na recepção*

Aqui está o código completo com algumas melhorias adicionais:

javascript
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
        Object.entries(schedule).forEach(([dia, horario]) => {
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


### Explicação das Alterações:

1. *Timeout antes de solicitar o cronograma atualizado*: Adicionamos um setTimeout de 500ms após enviar a nova programação para garantir que o servidor tenha tempo para processar e salvar os dados antes de solicitarmos a atualização.

2. *Função atualizarTabelaSchedule*: Esta função foi definida de forma a limpar a tabela existente e popular com os novos dados recebidos do servidor.

3. *Verificação do estado do WebSocket*: Garanta que o WebSocket esteja aberto antes de enviar qualquer mensagem.

Com essas mudanças, ao clicar em "Cadastrar Horário", a nova programação será enviada ao servidor, e a scheduleTable será atualizada com os novos dados.
