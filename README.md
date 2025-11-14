### 1. Clone repository:
~~~bash
git clone https://github.com/Hajatiana032/Marika.mg.git
~~~

### 2. Install composer:
~~~bash
composer install
~~~

### 3. Create database:
~~~bash
php bin\console doctrine:database:create
~~~

### 4. Execute migration:
~~~bash
php bin\console migration:migrate
~~~

### 5. Migrate migrations:
~~~bash
php bin\console doctrine:migration:migrate
~~~

### 6. Launch fixtures:
~~~bash
php bin\console doctrine:fixture:load
~~~

### 7. Execute symfony server:
~~~bash
symfony serve
~~~
