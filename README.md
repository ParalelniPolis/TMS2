# TMS2
Tenant Management System v2 for Paralelní Polis - system for regular Bitcoin and Litecoin payments

## Stack
Production is running on PHP 5.6 and MySQL 5.7

## Install (DEV only)
0. get PHP v5.6 with PDO&mbstring + Docker + docker-compose somewhere
1. run MySQL+Adminer Docker containers: `docker-compose up`
2. open Adminer in `http://localhost:8080/?server=db&username=root`, connect with login: `root`, pass: `root` and import `dbinit.sql` file into mysql
3. copypaste `default_config.php` into `config.php` and set your values (not needed to change anything)
3. run PHP build-in server: `php -S localhost:8000`
4. visit `localhost:8000`