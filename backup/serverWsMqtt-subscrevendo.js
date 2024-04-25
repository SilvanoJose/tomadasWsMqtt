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
  console.log("Cliente WebSocket conectado");
  wsClients.add(ws);

  // Remove o cliente da lista quando a conexão é fechada
  ws.on('close', () => {
    console.log("Cliente WebSocket desconectado");
    wsClients.delete(ws);
  });
});

// Quando uma mensagem é recebida via MQTT, retransmita para todos os clientes WebSocket
client.on('message', (topic, message) => {
  console.log(`Mensagem do MQTT [${topic}]: ${message.toString()}`);
  wsClients.forEach(client => {
    client.send(JSON.stringify({ topic, message: message.toString() }));
  });
});

