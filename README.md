## After starting Docker, run the following commands to set up the network and import the database:

```sh
sudo docker network create laravel-net-pos
sudo docker network connect laravel-net-pos laravel-app-pos
sudo docker exec -i sql-server-pos /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'YourStrong!Passw0rd' -C -i /var/opt/mssql/data/spa.sql
```