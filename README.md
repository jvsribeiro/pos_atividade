# Agenda de Contatos com Docker na AWS

Projeto desenvolvido para a atividade pratica de Docker na AWS, reutilizando a imagem da Aula 07 como base e executando Apache + PHP + MySQL no mesmo container.

## Arquitetura

- Container unico com Apache, PHP e MySQL
- Aplicacao PHP servida em `/var/www/html`
- Banco MySQL persistido fora do container
- Deploy da aplicacao incorporado na imagem por meio de `git clone`

## Imagem base da Aula 07

Imagem utilizada como base:

`joaodockeiro/webserver-ubuntu:1.0`

## Repositorio Git utilizado

Repositorio publico exigido pela atividade:

[https://github.com/jvsribeiro/pos_atividade](https://github.com/jvsribeiro/pos_atividade)

O Dockerfile faz o clone desse repositorio e copia automaticamente:

- `cadastro_contatos.php`
- `banco_contatos.sql`

para:

`/var/www/html`

## Arquivos obrigatorios no GitHub

Antes de gerar a imagem final, envie para o repositorio GitHub:

- `cadastro_contatos.php`
- `banco_contatos.sql`

Sem esses dois arquivos no repositorio, o build falha de proposito para manter a aderencia ao enunciado.

## Build da imagem final

Exemplo de build:

```bash
docker build -t joaodockeiro/agenda-contatos:2.0 .
```

## Publicacao no Docker Hub

Padrao utilizado:

`usuario/nomeimagem:versao`

Exemplo adotado:

`joaodockeiro/agenda-contatos:2.0`

Comandos:

```bash
docker login
docker push joaodockeiro/agenda-contatos:2.0
```

URL sugerida para entrega no Moodle:

[https://hub.docker.com/r/joaodockeiro/agenda-contatos](https://hub.docker.com/r/joaodockeiro/agenda-contatos)

## Execucao na EC2 Ubuntu

Comando completo `docker run` conforme o requisito da atividade:

```bash
docker run -d \
  -p 80:80 \
  -v /home/ubuntu/mysql_data:/var/lib/mysql \
  --name meu_container \
  joaodockeiro/agenda-contatos:2.0
```

## Persistencia do MySQL

A persistencia foi aplicada somente ao banco MySQL:

```bash
-v /home/ubuntu/mysql_data:/var/lib/mysql
```

Isso garante que:

- os arquivos da aplicacao PHP e SQL ficam embutidos na imagem
- apenas os dados do MySQL permanecem na EC2 mesmo que o container seja removido
- a aplicacao nao depende de volume para `/var/www/html`

## URL de acesso

Depois de executar o container na EC2, o acesso esperado e:

`http://IP_PUBLICO_DA_EC2`

Substitua pelo IP publico real da sua instancia AWS no README do Docker Hub.

## Funcionalidades da aplicacao

- cadastro de nome e telefone
- validacao de telefone no formato `(xx) x xxxx-xxxx`
- listagem dos contatos cadastrados

## Resumo da solucao

- imagem da Aula 07 reutilizada como base
- instalacao de `git` e `php-mysql` na imagem derivada
- clone automatico do repositorio Git do aluno durante o build
- copia automatica dos arquivos para `/var/www/html`
- inicializacao do MySQL com importacao do script SQL apenas quando necessario
- persistencia aplicada somente ao diretorio de dados do MySQL
