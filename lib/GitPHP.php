<?php
/*
 * GitPHP - Un objeto de utileria para el manejo del Git con PHP
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
 
#abstract class GitPhp {
class GitPHP {
	public $files_queue = array();
	#########
	public function __construct()
	{
		if (!defined('OS'))
		{
			if( strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ){
				define('OS','WIN');
				define('DS','\\');
			}else{
				define('OS','UNIX');
				define('DS','/');
			}
		}
	}
	/**
	 * Descripción de la función
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	function getConfigParam($selector) {
		$param = explode("\n",`git config $selector`);
		if(isset($param[0]))
			return $param[0];
		else
			return false;
	}
	/**
	 * Descripción de la función
	 *
	 * @param string $hash
	 * @return array $files lista de archivos
	 * @access publico
	 * @link 
	 */
	function filesByCommit( $hash, $sort = false ) {
		$cmd  = sprintf("git log -1 --name-only --pretty=format:'' %s",$hash);
		$files = explode("\n", `$cmd`);
		while( key($files) !== null ){
			if( $files[key($files)] == "" ){
				unset( $files[key($files)] );
				continue;
			}
			next($files);
		}
		if($sort)
			sort($files);
		return $files;
	}
	
	/**
	 * Regresa la lista de archivos que sufrieron un cambio entre hash1 y hash2
	 *
	 * @param string $hash1 clave sha1 del commit inicial
	 * @param string $hash2 clave sha1 del commit final
	 * @return array $files lista unica de archivos
	 * @access publico
	 * @link
	 */
	function filesBetweenCommits($hash1, $hash2){
		$hashes = $this->hashesBetween($hash1,$hash2);
		$files = array();
		foreach ($hashes as $h) 
		{
			$newsFiles = $this->filesByCommit($h);
			#array merge
			foreach($newsFiles as $newFile)
				if( !in_array($newFile,$files))
					$files[] = $newFile;
		}
		sort($files);
		return $files;
	}
	/**
	 * Devuelve los hashes entre el $hash1 y el $hash2 incluyendo estos
	 *
	 * @param string $hash1 hash inicial.
	 * @param string $hash2 hash final.
	 * @param string $order ordena los hashes en asendencia ASC o desendencia DESC segun como se fueron agregando.
	 * @return mixed $hashes bool false en caso de error array hashes en caso contrario
	 * @access publico
	 * @link 
	 */
	function hashesBetween($hash1,$hash2, $order='ASC') {
		if( !$this->validHash($hash1) || !$this->validHash($hash2) )
			return false;
		if( !in_array(strtoupper($order), array('ASC','DESC')))
			return false;
		$cmd  = sprintf("git rev-list %s..%s",$hash1, $hash2);
		$hs = explode("\n", `$cmd`);
		$hashes = array();
		foreach($hs as $h) {
			if( $this->validHash($h) )
				$hashes[] = $h;
		}
		$hashes[] = $hash1;
		if( strtoupper($order) == 'ASC' )
			$hashes = array_reverse($hashes);
		return $hashes;
	}
	/**
	 * Revisa que sea un hash valido(40 caracteres hexadecimales)
	 *
	 * @param string $hash a revisar
	 * @return boolean true en caso de ser valido false en caso contrario
	 * @access publico
	 * @link
	 */
	function validHash($hash){
		$h = "".$hash;
		return strlen($h) == 40;
		return preg_match('%[0-9a-f]{40}%i',$h)? true : false;
	}
	/**
	 * Regresa el hash del ultimo commit
	 *
	 * @return string $hash 
	 * @access publico
	 * @link
	 */
	function lastCommitHash() {
		return $this->prevCommitHash();
	}
	/**
	 * Regresa el hash del commit anterior numero $prev_num
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	function prevCommitHash($prev_num =  1){
		if( !is_numeric($prev_num) || $prev_num<1 )
			return false;
		$cms = sprintf("git log --pretty=format:\"%s\" -%d | tail -1",'%H',$prev_num);
		return `$cms`;
	}
	
	/**
	 * Devuelve el valor(es) del elemento solicitado en $var_name
	 * 
	 * Ejemplos:
	 * print_r( GitPHP::config('user'));
	 * Array
	 * (
	 *     [user.name] => myUserName
	 *     [user.email] => email@server.com
	 * )
	 * 
	 * print_r( GitPHP::config('user.name'));
	 * myUserName
	 * 
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	static function config($var_name) {
		$variables=array();
		preg_match_all(
					'%^'.str_replace('.','\.',$var_name).'.*=.*$%ismU', 
					`git config --list`,
					$variables);
		if(!empty($variables[0])){
			if(count($variables[0]) == 1)
				return preg_replace('%[^=].*=(.*)$%','\1',$variables[0][0]);
			$variables['data']=array();
			foreach($variables[0] as $v){
				$new_data = explode('=',$v);
				$variables['data'][$new_data[0]] = $new_data[1];
			}
			return $variables['data'];
		}
		return null;
	}
}

