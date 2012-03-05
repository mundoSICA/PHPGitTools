#Herramientas para la administración de proyecto.

Por el momento estamos trabajando, tenemos algunas dudas respecto al como debera ser la estructura del proyecto, favor de regresar pronto.

##Estructura actual:
	.
	|-- run.php <-- arrancador
	|-- docs <-- documentacion
	|-- hooks <-- lista de ganchos disponibles
	|   `-- empty
	|-- lib <-- librerias
	|   |-- GitPHP.php <-- nucleo
	|   `-- services <-- servicios agregados
	|       |-- Email.php <-- Soporte para email
	|       |-- Ftp.php <-- soporte para FTP
	|       |-- FillezillaQueue.php <--Manejo de la cola de archivos del Filezilla.
	|       `-- Service.php <-- Nucleo Libreria de Servicios
	`-- README.md

##OJO: Documentación pendiente.

##Instalación.

##Uso.

###Configuración.

###Ejemplo1. Publicando una rama por FTP

- Publicando una rama(por defecto public_html) de un proyecto al empujar cambios(push)
- Generando un gancho (hooks).

###Ejemplo2. Notificaciones por correo electronico.

Ejemplo de sessiones con `smtp` desde el servidor de **gmail**

###Ejemplo3. Autogenerador de documentación.

Generando automaticamente la documentación de un proyecto con git.


#Documentación para desarrolladores.

##Creando mis propios ganchos(hooks).

##Reportando errores.

##Traducciones.

