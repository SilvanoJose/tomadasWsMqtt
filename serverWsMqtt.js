const WebSocketServer = require('ws').Server;
const WebSocket = require('ws');
const mqtt = require('mqtt');
const wss = new WebSocketServer({ port: 8080 });
console.log("Servidor WebSocket rodando na porta 8080");
const { MongoClient } = require('mongodb'); // Adicionada para utilizar o MongoDB Atlas
const uri = "mongodb+srv://silvanojb:Ze560003@cluster0.rot0a49.mongodb.net/?retryWrites=true&w=majority"; // Adicionada para definir a URI de conexão ao MongoDB Atlas
const clientMongo = new MongoClient(uri, { useNewUrlParser: true, useUnifiedTopology: true }); // Criado um novo cliente MongoDB
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
  'silvanojose.tcc/schedule',
  'silvanojose.tcc/serialprints'
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


async function salvarNoMongoDB(topic, message) { // Adicionada para salvar as mensagens no MongoDB
  try {
    const db = clientMongo.db('silvanojose'); // Seleciona o banco de dados
    const collection = db.collection('tomadas'); // Seleciona a coleção
    const document = {
      topic: topic, // Tópico do MQTT
      message: message, // Mensagem recebida do MQTT
      timestamp: new Date() // Timestamp da mensagem
    };
    await collection.insertOne(document); // Insere o documento na coleção
    console.log(`Mensagem do tópico ${topic} salva no MongoDB.`); // Confirmação no console
  } catch (err) {
    console.error('Erro ao salvar no MongoDB:', err); // Tratamento de erro
  }
}


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
            //client.send(JSON.stringify({ tipo: 'schedule', data: scheduleData }));
            client.send(JSON.stringify({ tipo: 'schedule', message: schedule })); // <-- Mude para enviar o objeto diretamente

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
          //ws.send(JSON.stringify({ tipo: 'schedule', message: schedule }));
          ws.send(JSON.stringify({ tipo: 'schedule', message: schedule })); // <-- Mude para enviar o objeto diretamente
        }
      } catch (err) {
        console.error("Erro ao parsear JSON do schedule:", err);
      }
    }
  });

  // Evento de mensagem WebSocket
  ws.on('message', function incoming(message) {
    try {
      const msgData = JSON.parse(message);
      console.log('Mensagem recebida:', msgData);

      // Verifica o tipo de mensagem recebida  
      if (msgData.tipo && msgData.tipo === 'getEstado') {
        console.log("Entrou na opção getEstado");

        console.log(`Solicitação de estado para o tópico ${msgData.topico}`);
        // Consulta o estado atual da tomada no arquivo e envia de volta ao cliente
        carregarEstadoTomada(msgData.topico, (err, estado) => {
          if (err) {
            console.error("Erro ao carregar estado da tomada", err);
          } else {
            // Se o estado contém o tópico, extraímos apenas o estado
            const estadoTomada = estado.includes(":") ? estado.split(":")[1] : estado;

            if (ws.readyState === WebSocket.OPEN) {
              ws.send(JSON.stringify({ topico: msgData.topico, message: estadoTomada }));
            }
            console.log(`Estado atual da tomada ${msgData.topico}:`, estado);
          }
        });

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

      } else if (msgData.tipo && msgData.tipo === 'cadastrarHorario') {
        console.log("Entrou na opção cadastrarHorário");
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
                ws.send(JSON.stringify({ tipo: 'schedule', message: schedule })); // <-- Mude para enviar o objeto diretamente
              }
            } catch (err) {
              console.error("Erro ao parsear JSON do schedule:", err);
            }
          }
        });

      } else {
        // Se a mensagem não for reconhecida como uma solicitação específica, publica-a no MQTT
        if (msgData.topic && msgData.message !== undefined) {
          client.publish(msgData.topic, msgData.message);
        } else {
          console.error("Mensagem recebida está malformada:", message);
        }
      }
    } catch (e) {
        console.error('Mensagem recebida está malformada:', message);
    }    
  });

  // Evento de fechamento de conexão WebSocket
  ws.on('close', () => {
    console.log("Cliente WebSocket desconectado");
    wsClients.delete(ws);
  });
});

// Evento de mensagem MQTT
client.on('message', (topic, message) => {
  console.log(`Mensagem do MQTT [${topic}]: ${message.toString()}`);
  // Notifica os clientes WebSocket sobre a mensagem recebida do MQTT
  wsClients.forEach(client => {
    if (client.readyState === WebSocket.OPEN) {
      client.send(JSON.stringify({ topic, message: message.toString() }));
    }
  });

  // Salva o conteudo da mensagem recebida
  salvarEstadoTomada(topic, message.toString());
});

// Evento de erro MQTT
client.on('error', error => {
  console.error("Erro na conexão MQTT:", error);
});

// Evento de erro WebSocket
wss.on('error', error => {
  console.error("Erro na conexão WebSocket:", error);
});

// Evento de mensagem MQTT
client.on('message', async (topic, message) => {
  const messageStr = message.toString();
  console.log(`Mensagem recebida. Tópico: ${topic}, Mensagem: ${messageStr}`);

  try {
    await salvarNoMongoDB(topic, messageStr);
  } catch (err) {
    console.error('Erro ao salvar mensagem no MongoDB:', err);
  }

  // Salva o estado recebido nos arquivos JSON
  salvarEstadoTomada(topic, messageStr);

  // Envia a mensagem para todos os clientes WebSocket conectados
  wsClients.forEach(client => {
    if (client.readyState === WebSocket.OPEN) {
      client.send(JSON.stringify({ topico: topic, message: messageStr }));
    }
  });
});


async function start() { // Adicionada para conectar ao MongoDB ao iniciar o servidor
  try {
    await clientMongo.connect(); // Conexão ao MongoDB Atlas
    console.log('Conectado ao MongoDB Atlas'); // Confirmação no console
  } catch (err) {
    console.error('Erro ao conectar ao MongoDB Atlas:', err); // Tratamento de erro
  }
}
start(); // Inicia a conexão ao MongoDB ao iniciar o servidor


