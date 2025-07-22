# API de Gerenciador de Projetos

### Passos de Instalação

1. Instale as dependências do Composer:

   ```bash
   composer install
   ```

2. Copie o arquivo `.env.example` para `.env`:

   ```bash
   cp .env.example .env
   ```

3. Gere a chave da aplicação:

   ```bash
   php artisan key:generate
   ```

4. Para que o armazenamento local funcione corretamente, execute o comando a seguir:

   ```bash
   php artisan storage:link
   ```

5. Execute as migrações:

   ```bash
   php artisan migrate
   ```

6. Execute a aplicação com o comando:
   
   ```bash
   php artisan serve
   ```
