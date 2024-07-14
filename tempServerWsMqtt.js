const WebSocketServer = require('ws').Server;
const WebSocket = require('ws');
const mqtt = require('mqtt');

const wss = new WebSocketServer({ port: 8080 });
console.log("Servidor WebSocket rodando na porta 8080");

const client = mqtt.connect('mqtt://broker.emqx.io');

const topicos = [
  'silvanojose.tcc/temperaturaBoxTomadas',
  'silvanojose.tcc/temperaturaTomada1',
  'silvanojose.tcc/temperaturaTomada2',
  'silvanojose.tcc/temperaturaTomada3',
  'silvanojose.tcc/temperaturaTomada4',
  'silvanojose.tcc/tomada1',
  'silvanojose.tcc/tomada2',
  'silvanojose.tcc/tomada3',
  'silvanojose.tcc/tomada4',
  'silvanojose.tcc/schedule'
];

// Ao conectar-se ao broker MQTT, inscreve-se nos tópicos especificados
client.on('connect', () => {
  console.log("Conectado ao broker MQTT");
  topicos.forEach(topico => {
    client.subscribe(topico, err => {
      if (!err) {
        console.log(`Inscrito no tópico: ${topico}`);
      }
    });
  });
});

// Conjunto para manter os clientes WebSocket
const wsClients = new Set();

// Bibliotecas para manipulação de arquivos e caminhos
const fs = require('fs');
const path = require('path');

// Diretório para armazenar os estados
const diretorioEstados = path.join(__dirname, 'estados');

// Função para salvar o estado dos arquivos manipulados
function salvarEstadoTomada(topico, estado) {
  const arquivoEstadoTomada = path.join(diretorioEstados, `${topico}.json`);
  const diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

  console.log(`Tentando salvar estado para o tópico: ${topico}`);
  console.log(`Estado recebido: ${estado}`);

  if (topico === 'silvanojose.tcc/schedule') {
    const estadoArray = estado.split(',');

    const diaSemanaIndex = parseInt(estadoArray[0], 10);
    const tomadaNumero = parseInt(estadoArray[1], 10);
    const agendamentoNumero = parseInt(estadoArray[2], 10);

    if (isNaN(diaSemanaIndex) || diaSemanaIndex < 0 || diaSemanaIndex > 6 ||
        isNaN(tomadaNumero) || tomadaNumero < 1 || tomadaNumero > 4 ||
        isNaN(agendamentoNumero) || agendamentoNumero < 1 || agendamentoNumero > 3) {
      console.error("Índice inválido:", diaSemanaIndex, tomadaNumero, agendamentoNumero);
      return;
    }

    let schedule;

    // Se o arquivo existe, carrega o conteúdo existente
    if (fs.existsSync(arquivoEstadoTomada)) {
      const conteudoAtual = fs.readFileSync(arquivoEstadoTomada, 'utf8');
      try {
        schedule = JSON.parse(conteudoAtual);
      } catch (err) {
        console.error("Erro ao parsear JSON:", err);
        return;
      }
    } else {
      schedule = {}; // Se o arquivo não existe, cria um objeto vazio
    }

    schedule[diasSemana[diaSemanaIndex]] = schedule[diasSemana[diaSemanaIndex]] || {};
    schedule[diasSemana[diaSemanaIndex]][`tomada${tomadaNumero}`] = schedule[diasSemana[diaSemanaIndex]][`tomada${tomadaNumero}`] || {};
    schedule[diasSemana[diaSemanaIndex]][`tomada${tomadaNumero}`][`agendamento${agendamentoNumero}`] = {
      horaLigada: estadoArray[3],
      minutoLigada: estadoArray[4],
      horaDesligada: estadoArray[5],
      minutoDesligada: estadoArray[6],
    };

    // Salva o agendamento no arquivo
    fs.writeFile(arquivoEstadoTomada, JSON.stringify(schedule, null, 2), err => {
      if (err) {
        console.error(`Erro ao salvar estado para o tópico ${topico}`, err);
      } else {
        console.log(`Schedule para o dia da semana no tópico ${topico} salvo com sucesso`);
        
        // Notificar imediatamente os clientes WebSocket sobre a atualização do schedule
        const scheduleData = JSON.stringify(schedule);
        wsClients.forEach(client => {
          if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify({ tipo: 'schedule', data: scheduleData }));
          }
        });
      }
    });

  } else {
    // Salva o estado da tomada em um arquivo
    const estadoParaSalvar = `${topico}:${estado}\n`;
    fs.writeFile(arquivoEstadoTomada, estadoParaSalvar, err => {
      if (err) {
        console.error(`Erro ao salvar estado para o tópico ${topico}`, err);
      } else {
        console.log(`Estado para o tópico ${topico} salvo com sucesso`);
      }
    });
  }
}

// Função para carregar os estatus das tomadas
function carregarEstadoTomada(topico, callback) {
  const arquivoEstadoTomada = path.join(diretorioEstados, `${topico}.json`);

  fs.readFile(arquivoEstadoTomada, 'utf8', (err, fileContent) => {
    if (err) {
      console.error(`Erro ao ler o arquivo de estado da tomada ${topico}`, err);
      callback(err, null);
    } else {
      console.log(`Estado da tomada ${topico} carregado do arquivo com sucesso`);
      callback(null, fileContent.trim()); // Removendo espaços em branco do início e do fim
    }
  });
}

// Função para carregar o valor das temperaturas
function carregarEstadoTemperatura(topico, callback) {
  const arquivoEstadoTemperatura = path.join(diretorioEstados, `${topico}.json`);

  fs.readFile(arquivoEstadoTemperatura, 'utf8', (err, fileContent) => {
    if (err) {
      console.error(`Erro ao ler o arquivo de estado da temperatura ${topico}`, err);
      callback(err, null);
    } else {
      console.log(`Estado da temperatura ${topico} carregado do arquivo com sucesso`);
      callback(null, fileContent.trim()); // Removendo espaços em branco do início e do fim
    }
  });
}

// Evento de conexão WebSocket
wss.on('connection', ws => {
  console.log("Cliente WebSocket conectado");
  wsClients.add(ws);

// Carrega os dados de agendamento ao conectar
  carregarEstadoTomada('silvanojose.tcc/schedule', (err, scheduleData) => {
    if (err) {
      console.error("Erro ao carregar o estado do schedule:", err);
    } else {
      try {
        const schedule = JSON.parse(scheduleData);
        if (ws.readyState === WebSocket.OPEN) {
          ws.send(JSON.stringify({ tipo: 'schedule', message: schedule }));
        }
      } catch (err) {
        console.error("Erro ao parsear JSON do schedule:", err);
      }
    }
  });

  // Evento de mensagem WebSocket
  ws.on('message', message => {
    try {
        const msgData = JSON.parse(message);
        console.log('Mensagem recebida:', data);

        if (msgData.tipo === 'schedule') {
            const schedule = data.message;
            if (validateSchedule(schedule)) {
                // Processa a mensagem de agendamento e salva
                console.log('Agendamento recebido e válido:', schedule);
                // Aqui você processaria e salvaria o agendamento

                // Envia uma resposta ao cliente
                ws.send(JSON.stringify({ tipo: 'schedule', message: [schedule] }));
            } else {
                console.error('Agendamento inválido:', schedule);
            }
        } else if (msgData.tipo && msgData.tipo === 'getTemperatura') {
            console.log("Entrou na opção getTemperatura");
            carregarEstadoTemperatura(msgData.topico, (err, estado) => {
              if (err) {
                console.error("Erro ao carregar estado da temperatura", err);
              } else {
                const partes = estado.split(":");
                const temperatura = partes.length > 1 ? partes[1] : null;
      
                const temperaturaNumerica = parseFloat(temperatura);
                console.log("TemperaturaNumerica convertida: ", temperaturaNumerica);
                if (!isNaN(temperaturaNumerica) && temperaturaNumerica >= -99.99 && temperaturaNumerica <= 99.99) {
                  if (ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                      topico: msgData.topico,
                      message: temperaturaNumerica.toString()
                    }));
                  }
                } else {
                  console.error("Formato de temperatura inválido ou fora do intervalo esperado:", temperatura);
                }
              }
            });
      
        } else if (msgData.tipo === 'cadastrarHorario') {
          // Lógica para cadastrar horário no MQTT
          const { diaSemana, tomada, agendamento, horaLigar, minutoLigar, horaDesligar, minutoDesligar } = msgData;
          const mensagem = `${diaSemana},${tomada},${agendamento},${horaLigar.padStart(2, '0')},${minutoLigar.padStart(2, '0')},${horaDesligar.padStart(2, '0')},${minutoDesligar.padStart(2, '0')}`;
          
          client.publish('silvanojose.tcc/schedule', mensagem);
          console.log(`Horário de acionamento automático cadastrado: ${mensagem}`);
  
        } else if (msgData.tipo === 'getSchedule') {
          // Enviar os dados atuais de agendamento ao cliente
          carregarEstadoTomada('silvanojose.tcc/schedule', (err, scheduleData) => {
            if (err) {
              console.error("Erro ao carregar o estado do schedule:", err);
            } else {
              try {
                const schedule = JSON.parse(scheduleData);
                if (ws.readyState === WebSocket.OPEN) {
                  ws.send(JSON.stringify({ tipo: 'schedule', message: schedule }));
                }
              } catch (err) {
                console.error("Erro ao parsear JSON do schedule:", err);
              }
            }
          });
                  
        } else if (msgData.tipo && msgData.tipo === 'getEstado') {
            console.log("Entrou na opção getEstado");
      
            console.log(`Solicitação de estado para o tópico ${msgData.topico}`);
            carregarEstadoTomada(msgData.topico, (err, estado) => {
              if (err) {
                console.error("Erro ao carregar estado da tomada", err);
              } else {
                const estadoTomada = estado.includes(":") ? estado.split(":")[1] : estado;
      
                if (ws.readyState === WebSocket.OPEN) {
                  ws.send(JSON.stringify({ topico: msgData.topico, message: estadoTomada }));
                }
                console.log(`Estado atual da tomada ${msgData.topico}:`, estado);
              }
            });
            
        } else {
            console.log('Tipo de mensagem desconhecida:', data.tipo);
        }
    } catch (e) {
        console.error('Mensagem recebida está malformada:', message);
    }
});

// Evento de fechamento de conexão WebSocket
ws.on('close', () => {
    console.log('Cliente desconectado.');
});
});

function validateSchedule(schedule) {
const { diaSemana, horaLigar, minutoLigar, horaDesligar, minutoDesligar } = schedule;
const isValidDay = diaSemana >= 0 && diaSemana <= 6;
const isValidHourOn = horaLigar >= 0 && horaLigar <= 23;
const isValidMinuteOn = minutoLigar >= 0 && minutoLigar <= 59;
const isValidHourOff = horaDesligar >= 0 && horaDesligar <= 23;
const isValidMinuteOff = minutoDesligar >= 0 && minutoDesligar <= 59;
return isValidDay && isValidHourOn && isValidMinuteOn && isValidHourOff && isValidMinuteOff;
}

// Evento de mensagem MQTT
client.on('message', (topic, message) => {
  console.log(`Mensagem do MQTT [${topic}]: ${message.toString()}`);
  
  // Verifica se a mensagem é no tópico de agendamento
  if (topic === 'silvanojose.tcc/schedule') {
    // Salva o conteúdo da mensagem recebida e envia aos clientes WebSocket
    salvarEstadoTomada(topic, message.toString());

    // Notifica os clientes WebSocket sobre a mensagem recebida do MQTT
    carregarEstadoTomada('silvanojose.tcc/schedule', (err, scheduleData) => {
      if (err) {
        console.error("Erro ao carregar o estado do schedule:", err);
      } else {
        try {
          const schedule = JSON.parse(scheduleData);
          wsClients.forEach(client => {
            if (client.readyState === WebSocket.OPEN) {
              client.send(JSON.stringify({ tipo: 'schedule', message: schedule }));
            }
          });
        } catch (err) {
          console.error("Erro ao parsear JSON do schedule:", err);
        }
      }
    });
  } else {
    // Notifica os clientes WebSocket sobre a mensagem recebida do MQTT
    wsClients.forEach(client => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(JSON.stringify({ topic, message: message.toString() }));
      }
    });

    // Salva o conteúdo da mensagem recebida
    salvarEstadoTomada(topic, message.toString());
  }
});

// Evento de erro MQTT
client.on('error', error => {
  console.error("Erro na conexão MQTT:", error);
});

// Evento de erro WebSocket
wss.on('error', error => {
  console.error("Erro na conexão WebSocket:", error);
});
