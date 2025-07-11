# README - Banco de Dados e Ambiente Local

Este repositório contém o script SQL para criar o banco de dados do projeto final de BD, além de instruções para rodar um ambiente PHP simples com MySQL via Docker.

## Como executar

### 1. Subir o MySQL com Docker

Certifique-se de ter o [Docker](https://www.docker.com/) instalado. No terminal, execute:

```sh
docker run --name mysql-pjdb -e MYSQL_ROOT_PASSWORD=senha123 -e MYSQL_DATABASE=pjdb -p 3306:3306 -d mysql:8
```

- `MYSQL_ROOT_PASSWORD=senha123`: define a senha do usuário root.
- `MYSQL_DATABASE=pjdb`: cria o banco de dados `pjdb`.

### 2. Importar o script SQL

Após o container estar rodando, importe o script:

```sh
docker exec -i mysql-pjdb mysql -uroot -psenha123 pjdb < criar-tabelas.sql
```

### 3. Rodar o servidor PHP embutido

Com o PHP instalado localmente, execute na pasta do projeto:

```sh
php -S localhost:8000
```

Acesse [http://localhost:8000](http://localhost:8000) no navegador para visualizar o projeto PHP.

---

