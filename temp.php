<?php
function salvarEstadoTomada(topico, estado) {
  const arquivoEstadoTomada = path.join(diretorioEstados, `${topico}.txt`);

  console.log(`Tentando salvar estado para o tópico: ${topico}`);
  console.log(`Estado recebido: ${estado}`);

  if (topico === 'silvanojose/schedule') {
    const estadoArray = estado.split(',');
    console.log(`Estado dividido: ${estadoArray}`);

    const diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    let novoEstado = '';

    // Verifica se o arquivo já existe
    if (fs.existsSync(arquivoEstadoTomada)) {
      console.log(`O arquivo ${arquivoEstadoTomada} existe`);
      const conteudoAtual = fs.readFileSync(arquivoEstadoTomada, 'utf8');
      const linhas = conteudoAtual.split('\n');

      console.log(`Conteúdo atual do arquivo: ${linhas}`);

      if (linhas.length < diasSemana.length) {
        console.log(`Arquivo existente não tem linhas suficientes. Recriando.`);
        novoEstado = criarNovoEstado(estadoArray, diasSemana);
      } else {
        // Atualiza as linhas referentes aos dias da semana existentes
        for (let i = 0; i < linhas.length; i++) {
          const linha = linhas[i].trim(); // remove espaços em branco
          if (!linha) continue; // Ignora linhas vazias

          const partes = linha.split(':');
          const dia = partes[0];
          const indiceDia = diasSemana.indexOf(dia);

          if (indiceDia !== -1 && indiceDia < estadoArray.length) {
            const novosValores = estadoArray.slice(1).join(','); // Obter todos os valores restantes após o primeiro índice
            linhas[i] = `${dia}:${novosValores}`; // Atualiza a linha
            console.log(`Linha atualizada: ${linhas[i]}`);
          } else {
            console.error(`Dia não encontrado: ${dia}`);
          }
        }

        novoEstado = linhas.join('\n');
      }
    } else {
      console.log(`O arquivo ${arquivoEstadoTomada} não existe, criando novo estado`);

      novoEstado = criarNovoEstado(estadoArray, diasSemana);
    }

    console.log(`Novo estado construído: ${novoEstado}`);

    fs.writeFile(arquivoEstadoTomada, novoEstado, err => {
      if (err) {
        console.error(`Erro ao salvar estado do tópico ${topico} no arquivo`, err);
      } else {
        console.log(`Schedule do dia da semana para o tópico ${topico} salvo com sucesso`);
      }
    });
  } else {
    // Para tópicos que não são "silvanojose/schedule"
    const estadoParaSalvar = `${topico}:${estado}\n`;
    fs.writeFile(arquivoEstadoTomada, estadoParaSalvar, err => {
      if (err) {
        console.error(`Erro ao salvar estado da tomada ${topico} no arquivo`, err);
      } else {
        console.log(`Estado da tomada ${topico} salvo com sucesso`);
      }
    });
  }
}

// Função para criar um novo estado quando o arquivo não existe ou está corrompido
function criarNovoEstado(estadoArray, diasSemana) {
  let novoEstado = '';
  for (let i = 0; i < diasSemana.length; i++) {
    if (i < estadoArray.length - 1) {
      const dia = diasSemana[i];
      const novosValores = estadoArray.slice(1).join(','); // Extrair valores do estadoArray
      novoEstado += `${dia}:${novosValores}\n`;
      console.log(`Nova linha criada para ${dia}: ${novoEstado}`);
    } else {
      console.log(`Índice fora do alcance para o dia: ${diasSemana[i]}`);
    }
  }
  return novoEstado;
}


Domingo:01,02,13,14
Segunda:02,03,13,14
Terça:11,12,13,14
Quarta:11,12,14,14
Quinta:03,04,05,06
Sexta:04,05,06,07
Sabado:05,066,07,08