
// Configuração do servidor WebSocket
const WebSocketServer = require('ws').Server;
const WebSocket = require('ws');
const mqtt = require('mqtt');

const wss = new WebSocketServer({ port: 8080 });
console.log("Servidor WebSocket rodando na porta 8080");

// Conecte-se ao broker MQTT
const client = mqtt.connect('mqtt://192.168.0.107');

const topicos = [
  'silvanojose/temperaturaBoxTomadas',
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

const arquivoEstadoTomada = path.join(__dirname, 'estadoTomada1.txt');

// Função para salvar o estado da tomada no arquivo
function salvarEstadoTomada(topico, estado) {
  const estadoParaSalvar = `${topico}:${estado}\n`;
  fs.writeFile(arquivoEstadoTomada, estadoParaSalvar, err => {
    if (err) {
      console.error("Erro ao salvar estado da tomada no arquivo", err);
    } else {
      console.log("Estado da tomada salvo no arquivo com sucesso");
    }
  });
}

// Evento para receber mensagens dos clientes WebSocket
wss.on('connection', ws => {
  ws.on('message', function incoming(message) {
    const messageAsString = message.toString();
    console.log("Recebido do cliente WebSocket:", messageAsString);
    const msgData = JSON.parse(messageAsString); // Renomeado de `data` para `msgData`

    if (msgData.tipo && msgData.tipo === 'getEstado') {
      console.log(`Solicitação de estado para o tópico ${msgData.topico}`);
      // Consulta o estado atual da tomada no arquivo e envia de volta ao cliente
      fs.readFile(arquivoEstadoTomada, 'utf8', (err, fileContent) => { // Alterado para `fileContent`
        if (err) {
          console.error("Erro ao ler o arquivo de estado da tomada", err);
        } else {
          console.log("Conteúdo do arquivo:", fileContent); // Alterado o log para `fileContent`
          const linhas = fileContent.split('\n'); // Alterado para usar `fileContent`
          console.log("Linhas do arquivo:", linhas);
          // Agora correto: Usaremos `msgData.topico` em vez de `data.topico`
          const estadoLinha = linhas.find(linha => linha.startsWith(`${msgData.topico}:`));
          console.log("Linha do estado da tomada:", estadoLinha); 
          if (estadoLinha) {
            const estado = estadoLinha.split(':')[1];
            if (ws.readyState === WebSocket.OPEN) {
              ws.send(JSON.stringify({ topico: msgData.topico, message: estado })); // Corrigindo para `msgData.topico`
            }
            console.log("Estado atual da tomada:", estado);
          }
        }
      });
    } else {
      if (msgData.topic && msgData.message !== undefined) { // Também renomeado para `msgData`
        client.publish(msgData.topic, msgData.message);
      } else {
        console.error("Mensagem recebida está malformada:", messageAsString);
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
