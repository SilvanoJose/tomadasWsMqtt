
// Configuração do servidor WebSocket
const WebSocketServer = require('ws').Server;
const WebSocket = require('ws');
const mqtt = require('mqtt');

const wss = new WebSocketServer({ port: 8080 });
console.log("Servidor WebSocket rodando na porta 8080");

// Conecte-se ao broker MQTT
const client = mqtt.connect('mqtt://192.168.0.101');

const topicos = [
  'silvanojose/temperaturaBoxTomadas',
  'silvanojose/temperaturaTomada1',
  'silvanojose/temperaturaTomada2',
  'silvanojose/temperaturaTomada3',
  'silvanojose/temperaturaTomada4',
  'silvanojose/tomada1',
  'silvanojose/tomada2',
  'silvanojose/tomada3',
  'silvanojose/tomada4'
];

// Quando conectado ao MQTT, assine os tópicos
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

// Mantém um conjunto de clientes WebSocket conectados
const wsClients = new Set();

const fs = require('fs');
const path = require('path');

// Diretório onde serão salvos os arquivos de estado das tomadas
const diretorioEstados = path.join(__dirname, 'estados');

// Função para salvar o estado da tomada em um arquivo
function salvarEstadoTomada(topico, estado) {
  const arquivoEstadoTomada = path.join(diretorioEstados, `${topico}.txt`);
  const estadoParaSalvar = `${topico}:${estado}\n`;
  
  fs.writeFile(arquivoEstadoTomada, estadoParaSalvar, err => {
    if (err) {
      console.error(`Erro ao salvar estado da tomada ${topico} no arquivo`, err);
    } else {
      console.log(`Estado da tomada ${topico} salvo no arquivo com sucesso`);
    }
  });
}

// Função para carregar o estado da tomada de um arquivo
function carregarEstadoTomada(topico, callback) {
  const arquivoEstadoTomada = path.join(diretorioEstados, `${topico}.txt`);
  
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

// Evento para receber mensagens dos clientes WebSocket
wss.on('connection', ws => {
  ws.on('message', function incoming(message) {
    const msgData = JSON.parse(message);

    if (msgData.tipo && msgData.tipo === 'getEstado') {
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
          console.log(`Teste se é aqui:Estado atual da tomada ${msgData.topico}:`, estado);
        }
      });
    } else {
      if (msgData.topic && msgData.message !== undefined) {
        client.publish(msgData.topic, msgData.message);
      } else {
        console.error("Mensagem recebida está malformada:", message);
      }
    }
  });

  console.log("Cliente WebSocket conectado");
  wsClients.add(ws);

  // Remove o cliente da lista quando a conexão é fechada
  ws.on('close', () => {
    console.log("Cliente WebSocket desconectado");
    wsClients.delete(ws);
  });
});


// Quando uma mensagem é recebida via MQTT, retransmita para todos os clientes WebSocket e salve o estado no arquivo
client.on('message', (topic, message) => {
  console.log(`Mensagem do MQTT [${topic}]: ${message.toString()}`);
  
  wsClients.forEach(client => {
    client.send(JSON.stringify({ topic, message: message.toString() }));
  });

  // Salva o estado da tomada no arquivo
  salvarEstadoTomada(topic, message.toString());
});

// Logs de erros
client.on('error', error => {
  console.error("Erro na conexão MQTT:", error);
});

wss.on('error', error => {
  console.error("Erro na conexão WebSocket:", error);
});
