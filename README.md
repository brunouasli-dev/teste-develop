# Teste de desenvolvimento

Este é um projeto Laravel que permite a gestão de contatos com geolocalização, incluindo busca e visualização dos contatos em um mapa.

## Funcionalidades

- **Autenticação de Usuários**: Protege o acesso às rotas da aplicação.
- **Geolocalização**: Visualiza os contatos em um mapa com base em seus endereços.
- **Busca de Contatos**: Pesquisa contatos pelo nome.
- **Adição de Novos Contatos**: Interface para adicionar novos contatos com seus dados geográficos.

## Requisitos

Antes de começar, certifique-se de ter as seguintes ferramentas instaladas em sua máquina:

- [Git](https://git-scm.com/)
- [PHP](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) e [NPM](https://www.npmjs.com/)

## Instalação

1. Clone o repositório:

    ```bash
    git clone https://github.com/brunouasli-dev/teste-develop.git
    cd seu-repositorio
    ```

2. Instale as dependências do PHP:

    ```bash
    composer install
    ```

3. Instale as dependências do NPM:

    ```bash
    npm install
    ```

4. Copie o arquivo de exemplo `.env` e configure suas variáveis de ambiente:

    ```bash
    cp .env.example .env
    ```

5. Gere a chave da aplicação:

    ```bash
    php artisan key:generate
    ```

6. Configure o arquivo `.env` com suas credenciais de banco de dados e outras configurações necessárias.

7. Execute as migrações para criar as tabelas no banco de dados:

    ```bash
    php artisan migrate
    ```

8. Opcionalmente, você pode popular o banco de dados com dados fictícios:

    ```bash
    php artisan db:seed
    ```

## Uso

1. Inicie o servidor de desenvolvimento:

    ```bash
    php artisan serve
    ```

2. Compile os assets do front-end:

    ```bash
    npm run dev
    ```

3. Acesse a aplicação no navegador:

    ```
    http://localhost:8000
    ```
## Teste com Postman

### Passo 1: Login

1. Abra o Postman.
2. Método: **POST**
3. URL: `http://localhost:8000/api/login`
4. Body: 
    - Tipo: **Raw**
    - Formato: **JSON**
    
    ```json
    {
        "email": "exemplo@gmail.com",
        "password": "teste123456"
    }
    ```

5. Resposta esperada:

    ```json
    {
        "token": "7|Fl45EAqKN2QP14l92R4JxGjqRgF5313bp490wumv2f08b66e"
    }
    ```

### Passo 2: Adicionar Contato

1. Método: **POST**
2. URL: `http://localhost:8000/api/contacts`
3. Body: 
    - Tipo: **Raw**
    - Formato: **JSON**
    
    ```json
    {
        "token": "Token recebido na resposta do login",
        "name": "Teste",
        "cpf": "00000000000",
        "phone": "99999999999",
        "address": "Endereço real (Se for incorreto vai falhar....)",
        "cep": "CEP real, se for incorreto vai falhar...",
        "city": "Cidade correta....",
        "state": "Estado correto, no máximo 2 letras"
    }
    ```

4. Resposta esperada:

    ```json
    {
        "name": "Teste",
        "cpf": "00000000000",
        "phone": "99999999999",
        "address": "Endereço",
        "cep": "CEP",
        "city": "Cidade",
        "state": "Estado",
        "latitude": "-25.5617771",
        "longitude": "-49.2523769",
        "user_id": 1,
        "updated_at": "2024-06-06T05:45:56.000000Z",
        "created_at": "2024-06-06T05:45:56.000000Z",
        "id": 1
    }
    ```

### Maneira Manual sem Criar Contatos pelo Postman

1. Obtenha o token conforme descrito anteriormente.
2. Vá até o arquivo:

    ```php
    // app/Http/Controllers/SearchController.php
    ```

3. Procure pela linha:

    ```php
    $token = '7|Fl45EAqKN2QP14l92R4JxGjqRgF5313bp490wumv2f08b66e';
    ```

4. Substitua pelo seu token.

5. Faça login na sua conta:

    ```
    http://localhost:8000/login
    ```

6. Navegue até:

    ```
    http://localhost:8000/map
    ```

    ou clique no perfil e após Map Search.

Use a aplicação à vontade.

---

**Nota:** Esta aplicação foi feita apenas para comprovar conhecimento na área de atuação, portanto, ela é feita apenas para demonstração e não está apta a ser usada em uma aplicação real.

