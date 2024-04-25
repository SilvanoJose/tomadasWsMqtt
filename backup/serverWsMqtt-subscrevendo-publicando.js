const WebSocketServer = require('ws').Server;
const mqtt = require('mqtt');

// Configuração do servidor WebSocket
const wss = new WebSocketServer({ port: 8080 });
console.log("Servidor WebSocket rodando na porta 8080");

// Conecte-se ao broker MQTT
const client = mqtt.connect('mqtt://192.168.0.112');

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

// Adiciona um cliente WebSocket à lista quando conecta
wss.on('connection', ws => {
  
  ws.on('message', function incoming(message) {
    const messageAsString = message.toString();
    console.log("Recebido do cliente WebSocket:", messageAsString);
    const data = JSON.parse(messageAsString);
  
    // Verifica se a mensagem tem os campos esperados
    if (data.tipo && data.topico) {
      // Lógica específica para o tipo "getEstado"
      if (data.tipo === 'getEstado') {
        // Aqui, você poderia consultar o estado atual e enviar de volta ao cliente
        console.log(`Solicitação de estado para o tópico ${data.topico}`);
        // Exemplo: client.publish(data.topico, "estadoAtual");
      }
    } else if (data.topic && data.message !== undefined) {
      client.publish(data.topic, data.message);
    } else {
      console.error("Mensagem recebida está malformada:", messageAsString);
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
/*
Portanto, você precisa de um trecho de código que 
faça isso. Dentro do evento wss.on('connection', ...), adicione:

javascript
ws.on('message', function incoming(message) {
  console.log("Recebido do cliente WebSocket:", message);
  // Parse the JSON message
  const data = JSON.parse(message);
  // Publish to MQTT
  client.publish(data.topic, data.message);
});
*/

// Quando uma mensagem é recebida via MQTT, retransmita para todos os clientes WebSocket
client.on('message', (topic, message) => {
  console.log(`Mensagem do MQTT [${topic}]: ${message.toString()}`);
  wsClients.forEach(client => {
    client.send(JSON.stringify({ topic, message: message.toString() }));
    console.log(`Mensagem enviada para cliente WebSocket`);
  });
});

// Logs de erros
client.on('error', error => {
  console.error("Erro na conexão MQTT:", error);
});

wss.on('error', error => {
  console.error("Erro na conexão WebSocket:", error);
});
