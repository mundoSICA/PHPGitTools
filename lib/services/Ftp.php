#!/usr/bin/env php
<?php
/*
 * Ftp - una extension de GitPHP para el manejo de repositorios remotos via FTP.
 * Algunas ideas extraidas del cliente FTP del Joomla 2.5
 * 
 * @link: https://github.com/joomla/joomla-cms/commits/master/libraries/joomla/client/ftp.php
 * 
 * Ejemplo de Uso:
 * -----------------------------------------------------------------
 * $ftp_client = new FtpGitClient();
 * $ftp_client->connect('my-server.com');
 * $ftp_client->login($user='user',$pass='secret');
 * $ftp_client->chdir('/public_html');
 * print_r( $ftp_client->ls() );
 * 
 * 
 * Copyright 2012 fitorec <programacion@mundosica.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
include_once 'Service.php';

class FtpGitClient extends Service {
	/**
	 * @var    resource  Socket resource
	 */
	private $_conn = null;

	/**
	 * @var    resource  Data port connection resource
	 */
	private $_dataconn = null;

	/**
	 * @var    array  Passive connection information
	 */
	private $_pasv = null;

	/**
	 * @var    string  Response Message
	 */
	private $_response = null;

	/**
	 * @var    integer  Timeout limit
	 */
	private $_timeout = 15;

	/**
	 * @var    integer  Transfer Type
	 */
	private $_type = null;
	/**
	 * @var    array  Array to hold ascii format file extensions
	 */
	private $_autoAscii = array(
		"asp",
		"bat",
		"c",
		"cpp",
		"csv",
		"h",
		"htm",
		"html",
		"shtml",
		"ini",
		"inc",
		"log",
		"php",
		"php3",
		"pl",
		"perl",
		"sh",
		"sql",
		"txt",
		"xhtml",
		"xml");
	/**
	 * Descripción de la función
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	function __construct() {
		parent::__construct();
		if (!extension_loaded('ftp'))
		{
			if (OS === 'WIN')
			{
				@ dl('php_ftp.dll');
			}
			else
			{
				@ dl('ftp.so');
			}
		}
		if (!defined('FTP_NATIVE'))
		{
			define('FTP_NATIVE', (function_exists('ftp_connect')) ? 1 : 0);
		}
	}
	
	/**
	 * Cierra la conexión FTP
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	public function __destruct() {
		@ftp_close($this->_conn);
		return true;
	}
	
	public function connect($host = '127.0.0.1', $port = 21)
	{
		// Variables iniciales
		$errno = null;
		$err = null;

		// Si ya fue conectado el recurso
		if (is_resource($this->_conn))
		{
			return true;
		}
		
		// Si tenemos soporte nativo FTP
		$this->_conn = @ftp_connect($host, $port, $this->_timeout);
		if ($this->_conn === false)
		{
			echo 'Revise su conexion con el host ' . $host . ':'. $port . "\n";
			return false;
		}
		//Fijamos el tiempo de la sesion ftp
		ftp_set_option($this->_conn, FTP_TIMEOUT_SEC, $this->_timeout);
		return true;
	}
	
	/**
	 * Method to login to a server once connected
	 *
	 * @param   string  $user  Username to login to the server
	 * @param   string  $pass  Password to login to the server
	 *
	 * @return  boolean  True if successful
	 *
	 */
	public function login($user = 'anonymous', $pass = '')
	{
		// Si todavia no existe la conexión la establecemos
		if ( !is_resource($this->_conn))
		{
			$this->connect();
		}
		// If native FTP support is enabled let's use it...
		if (@ftp_login($this->_conn, $user, $pass) === false)
		{
			echo 'Error en el login para el usuario ' . $user . "\n";
			return false;
		}
		return true;
	}
	
	public function ls($dir = null){
		if( !$dir )
			$dir = $this->pwd();
		return ftp_nlist($this->_conn,$dir);
	}
	
	public function dowload($file){
		return ftp_get($this->_conn, './'.$file, $file, $this->_getMode($file));
	}
	
	private function _getMode($fileName){
		$dotPosition = strrpos($fileName, '.') + 1;
		$ext = substr($fileName, $dotPosition);
		if (in_array($ext, $this->_autoAscii))
				return FTP_ASCII;
		return FTP_BINARY;
	}
	
	/**
	 * Regresa el directorio actual en donde se encuentra conectado
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	function pwd() {
		if (($ret = @ftp_pwd($this->_conn)) === false)
		{
			echo 'Error en la conexión' . "\n";
			return false;
		}
		return $ret;
	}
	
	/**
	 * Detecta y devuelve el systema operativo remoto
	 *
	 * @return string $os el nombre del sistema operativo remoto
	 * @access publico
	 * @link
	 */
	function syst() {
		$os = false;
		if (($os = @ftp_systype($this->_conn)) === false)
		{
			echo 'Error en detectar el sistema remoto' . "\n";
		}
		return $os;
	}
	
	/**
	 * Method to change the current working directory on the FTP server
	 *
	 * @param   string  $path  Path to change into on the server
	 * @return  boolean True if successful
	 */
	public function chdir($path){
		if (@ftp_chdir($this->_conn, $path) === false)
		{
			echo 'No fue posible cambiar de directorio' . "\n";
			return false;
		}
		return true;
	}
	
	/**
	 * Method to reinitialise the server, ie. need to login again
	 *
	 * NOTE: This command not available on all servers
	 * @return  boolean  True if successful
	 */
	public function reinit()
	{
		if (@ftp_site($this->_conn, 'REIN') === false)
		{
			echo 'Error en restablecer la conexión' . "\n";
			return false;
		}
		return true;
	}
	
	/**
	 * Method to rename a file/folder on the FTP server
	 *
	 * @param   string  $from  Path to change file/folder from
	 * @param   string  $to    Path to change file/folder to
	 * @return  boolean  True if successful
	 *
	 */
	public function rename($from, $to)
	{
		if (@ftp_rename($this->_conn, $from, $to) === false)
		{
			echo 'No se pudo renombrar el archivo' . "\n";
			return false;
		}
		return true;
	}
	/**
	 * Method to change mode for a path on the FTP server
	 *
	 * @param   string  $path  Path to change mode on
	 * @param   mixed   $mode  Octal value to change mode to, e.g. '0777', 0777 or 511 (string or integer)
	 * @return  boolean  True if successful
	 *
	 */
	public function chmod($path, $mode)
	{
		// If no filename is given, we assume the current directory is the target
		if ($path == '')
		{
			$path = '.';
		}
		// Convert the mode to a string
		if (is_int($mode))
		{
			$mode = decoct($mode);
		}
		if (@ftp_site($this->_conn, 'CHMOD ' . $mode . ' ' . $path) === false)
		{
			echo 'Error al intentar cambiar los permisos';
			return false;
		}
		return true;
	}
	/**
	 * Method to delete a path [file/folder] on the FTP server
	 *
	 * @param   string  $path  Path to delete
	 * @return  boolean  True if successful
	 *
	 */
	public function delete($path)
	{
		if (@ftp_delete($this->_conn, $path) === false)
		{
			if (@ftp_rmdir($this->_conn, $path) === false)
			{
				echo 'Error al intentar borrar el archivo '.$path."\n";
				return false;
			}
		}
		return true;
	}
	/**
	 * Method to create a directory on the FTP server
	 *
	 * @param   string  $path  Directory to create
	 * @return  boolean  True if successful
	 */
	public function mkdir($path)
	{
		if (@ftp_mkdir($this->_conn, $path) === false)
		{
			echo 'Error al intentar crear el directorio '.$path."\n";
			return false;
		}
		return true;
	}
	/**
	 * Method to create an empty file on the FTP server
	 * 
	 * @param   string  $path  Path local file to store on the FTP server
	 * @return  boolean  True if successful
	 *
	 */
	public function create($path)
	{
		if (@ftp_pasv($this->_conn, true) === false)
		{
			echo 'Error en activar el modo pasivo';
			return false;
		}
		$buffer = fopen('buffer://tmp', 'r');
		$successful = true;
		if (@ftp_fput($this->_conn, $path, $buffer, FTP_ASCII) === false)
		{
			echo 'Error al intentar crear un archivo en el servidor';
			$successful = false;
			
		}
		fclose($buffer);
		return $successful;
	}
	
	/**
	 * Method to store a file to the FTP server
	 *
	 * @param   string  $local   Path to local file to store on the FTP server
	 * @param   string  $remote  FTP path to file to create
	 * @return  boolean  True if successful
	 *
	 */
	public function update($local, $remote = null)
	{
		// If remote file is not given, use the filename of the local file in the current
		// working directory.
		if( !file_exists($local) ){
			echo 'El archivo que intenta subir no existe, favor de verificar';
		}
		
		if ($remote == null)
		{
			$remote =  $this->pwd(). DS .basename($local);
		}
		
		// Determine file type
		$mode = $this->_getMode($remote);
		
		// Turn passive mode on
		if (@ftp_pasv($this->_conn, true) === false)
		{
			echo 'Error en activar el modo pasivo';
			return false;
		}
		if (@ftp_put($this->_conn, $remote, $local, $mode) === false)
		{
			echo 'Error en subir el archivo '.$local.' en '.$remote;
			return false;
		}
		return true;
	}
	/**
	 * Method to write a string to the FTP server
	 *
	 * @param   string  $remote  FTP path to file to write to
	 * @param   string  $buffer  Contents to write to the FTP server
	 * @return  boolean  True if successful
	 */
	public function write($remote, $buffer)
	{
		// Determine file type
		$mode = $this->_getMode($remote);
		// Turn passive mode on
		if (@ftp_pasv($this->_conn, true) === false)
		{
			echo 'Error en activar el modo pasivo';
			return false;
		}
		$tmp_file = '/tmp/'.basename($remote);
		$tmp = fopen($tmp_file, 'w');
		fwrite($tmp, $buffer);
		rewind($tmp);
		$successful = true;
		if (@ftp_put($this->_conn, $remote, $tmp_file, $mode) === false)
		{
			echo 'Error al intentar escribir mediante un buffer en el servidor';
			$successful =  false;
		}
		fclose($tmp);
		unlink('/tmp/'.basename($remote));
		return $successful;
	}
	/**
	 * Send command to the FTP server and validate an expected response code
	 *
	 * @param   string  $cmd               Command to send to the FTP server
	 * @param   mixed   $expectedResponse  Integer response code or array of integer response codes
	 * @return  boolean  True if command executed successfully
	 */
	public function putCmd($cmd, $expectedResponse)
	{
		// Make sure we have a connection to the server
		if (!is_resource($this->_conn))
		{
			$this->connect();
		}
		// Send the command to the server
		if (!fwrite($this->_conn, $cmd . "\r\n"))
		{
			echo 'Error al enviar un comando al servidor.';
		}

		return $this->_verifyResponse($expectedResponse);
	}
}
