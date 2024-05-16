
// Configuração do servidor WebSocket
const WebSocketServer = require('ws').Server;
const WebSocket = require('ws');
const mqtt = require('mqtt');

const wss = new WebSocketServer({ port: 8080 });
console.log("Servidor WebSocket rodando na porta 8080");

// Conecte-se ao broker MQTT
const client = mqtt.connect('mqtt://broker.emqx.io');

const topicos = [
  'silvanojose/temperaturaBoxTomadas',
  'silvanojose/temperaturaTomada1',
  'silvanojose/temperaturaTomada2',
  'silvanojose/temperaturaTomada3',
  'silvanojose/temperaturaTomada4',
  'silvanojose/tomada1',
  'silvanojose/tomada2',
  'silvanojose/tomada3',
  'silvanojose/tomada4',
  "silvanojose/schedule"
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

/*
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
*/
////Nova função salvar arquivos
// Função para salvar o estado da tomada em um arquivo


function salvarEstadoTomada(topico, estado) {
  const arquivoEstadoTomada = path.join(diretorioEstados, `${topico}.json`);
  const diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

  console.log(`Tentando salvar estado para o tópico: ${topico}`);
  console.log(`Estado recebido: ${estado}`);

  if (topico === 'silvanojose/schedule') {
    const estadoArray = estado.split(',');

    const diaSemanaIndex = parseInt(estadoArray[0], 10);
    if (isNaN(diaSemanaIndex) || diaSemanaIndex < 0 || diaSemanaIndex > 6) {
      console.error("Índice do dia da semana inválido:", diaSemanaIndex);
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

    // Atualiza ou adiciona a informação do dia da semana
    schedule[diasSemana[diaSemanaIndex]] = {
      horaLigada: estadoArray[1],
      minutoLigada: estadoArray[2],
      horaDesligada: estadoArray[3],
      minutoDesligada: estadoArray[4],
    };

    // Salva o arquivo como JSON
    fs.writeFile(arquivoEstadoTomada, JSON.stringify(schedule, null, 2), err => {
      if (err) {
        console.error(`Erro ao salvar estado para o tópico ${topico}`, err);
      } else {
        console.log(`Schedule para o dia da semana no tópico ${topico} salvo com sucesso`);
      }
    });

  } else {
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



////Fim nova função salvar arquivos
// Função para carregar o estado da tomada de um arquivo
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

// Função para carregar o estado da temperatura de um arquivo
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

// Evento para receber mensagens dos clientes WebSocket
wss.on('connection', ws => {
  ws.on('message', function incoming(message) {
    const msgData = JSON.parse(message);
    if (msgData.tipo && msgData.tipo === 'getEstado') {
        console.log("Entrou na opção getEstado");
        //  block of code to be executed if condition1 is true
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
        //  block of code to be executed if the condition1 is false and condition2 is true
            console.log("Entrou na opção getTemperatura");
            // Se for um tópico de temperatura, carrega o estado inicial da temperatura
            carregarEstadoTemperatura(msgData.topico, (err, estado) => {
                if (err) {
                    console.error("Erro ao carregar estado da temperatura", err);
                } else {
              // Extrai a parte após o ":" que deveria representar a temperatura
              const partes = estado.split(":");
              const temperatura = partes.length > 1 ? partes[1] : null;

              // Verificação adicional para garantir que o valor é numérico e está dentro do intervalo esperado
              const temperaturaNumerica = parseFloat(temperatura);
              console.log("TemperaturaNumerica convertida: " ,temperaturaNumerica);
              if (!isNaN(temperaturaNumerica) && temperaturaNumerica >= -99.99 && temperaturaNumerica <= 99.99) {
                  if (ws.readyState === WebSocket.OPEN) {
                      ws.send(JSON.stringify({ topico: msgData.topico, message: temperaturaNumerica.toString() }));
                  }
              } else {
                  console.error("Formato de temperatura inválido ou fora do intervalo esperado:", temperatura);
              }        
                }
            });

      } else if (msgData.tipo && msgData.tipo === 'cadastrarHorario') {
        console.log("Entrou na opção cadastrarHorário");
        // Se a mensagem for do tipo 'cadastrarHorario', publica as informações no tópico 'silvanojose/config/schedule'
        const { diaSemana, horaLigar, minutoLigar, horaDesligar, minutoDesligar } = msgData;
        const mensagem = `${diaSemana},${horaLigar.padStart(2, '0')},${minutoLigar.padStart(2, '0')},${horaDesligar.padStart(2, '0')},${minutoDesligar.padStart(2, '0')}`;
        
        client.publish('silvanojose/schedule', mensagem);
        console.log(`Horário de acionamento automático cadastrado: ${mensagem}`);
      } else {
        //  block of code to be executed if the condition1 is false and condition2 is false
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
