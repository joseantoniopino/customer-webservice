## Instrucciones

Esta sencilla aplicación genera un archvo CSV en storage/app/reports con los datos de los usuarios
que nos vienen dados desde un webservice y un xml.

El comando para lanzar el script es: `php artisan report:users` El comando lee el archivo UsersReport.php
situado en app/Console/Commands
- Lo primero que nos va a preguntar es la ruta absoluta del archivo xml. Si le damos mal la ruta nos dirá que
el archivo no existe y preguntará si queremos continuar de todas formas.
- Al final de la ruta no es necesario añadir la extensión .xml, ya que el sript lo hará por nosotros.
Si la añadimos tampoco pasa nada.
- Si le decimos que si, nos generará el csv solo con los datos del webservice.
- Si le decimos que no, no generará ningún archivo.
- Al finalizar la ejecución, aparte de crear el archivo csv nos pintará en la consola una tabla con
el contenido del csv.

#### Librerías utilizadas
Tan solo se ha utilizado una librería externa al ecosistema de Laravel, la cual ya está en el composer
y se puede descargar desde github aquí -> [SoapBox](https://github.com/SoapBox/laravel-formatter)

El archivo xml se puede encontrar en public/data/data.xml y el csv resultante se guardará en storage/app/reports
