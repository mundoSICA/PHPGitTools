<?php
/*
 * GitPHP - Un objeto de utileria para el manejo del Git con PHP
 *
 * Copyright 2012 fitorec <programacion@mundosica.com>
 *
 * This program is free software; you can redistribute it and/or modif (y
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
 * along with this program; if ( not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fif (th Floor, Boston,
 * MA 02110-1301, USA.
 */

defined('PHPGIT_RUNNING') || die;

class GitPHP
{
  #########

 public function __construct( ) {
 }

/**
 * Descripción de la función
 *
 * @param string $hash
 * @return array $files lista de archivos
 * @access publico
 * @link 
 */

 public function filesByCommit( $hash, $sort = false ) {
  $cmd  = sprintf("git log -1 --name-only --pretty=format:'' %s", $hash);
  $files = explode("\n", `$cmd`);
  while ( key($files) !== null ) {
   if ( $files[key($files)] == "" ) {
    unset( $files[key($files)] );
    continue;
   }
   next($files);
  }
  if ($sort) {
   sort($files);
  }
  return $files;
 }//end filesByCommit

	/**
	 * Descripción de la función
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	function dir() {
		return GitPHP::exec('rev-parse --show-toplevel');
	}
	
	/**
	 * Ejecuta un comando del git con parametros $params
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	static public function exec($params) {
		$cmd_result = ltrim(rtrim(`git $params`));
		$cmd_result = explode("\n",$cmd_result);
		$result=array();
		foreach($cmd_result as $line){
			$line=rtrim(ltrim($line));
			if(strlen($line)>1)
				$result[]=$line;
		}
		if( count($result) > 1 )
			return $result;
		if( count($result) == 1 )
			return $result[0];
		return null;
	}
/**
 * Regresa la lista de archivos que sufrieron un cambio entre hash1 y hash2
 *
 * @param string $hashInit clave sha1 del commit inicial
 * @param string $hashEnd clave sha1 del commit final
 * @return array $files lista unica de archivos
 * @access publico
 * @link
 */

 public function filesBetweenCommits($hashInit, $hashEnd) {
  $cmd  = sprintf("git diff --name-status %s %s",$hashInit, $hashEnd);
  $files = explode("\n", `$cmd`);
  $dir = $this->dir();
  sort($files);
  $filesBetween = Array();
  foreach($files as $file){
	  $sub=array();
	  if( preg_match_all('%^([A-Z])\s*(.*)$%',$file,$sub) ){
		if( count($sub) == 3){
			$filesBetween[] = array(
					'file'=>$sub[2][0],
					'status'=>$sub[1][0]
			);
			$index = count($filesBetween) - 1;
			$filesBetween[$index]['size'] = 0;
			if( $filesBetween[$index]['status'] != 'D' )
				$filesBetween[$index]['size'] = filesize($dir. DS . $filesBetween[$index]['file']);
		}
	}
  }
  return $filesBetween;
 }//end filesBetweenCommits

/**
 * Devuelve los hashes entre el $hashInit y el $hashEnd incluyendo estos
 *
 * @param string $hashInit hash inicial.
 * @param string $hashEnd hash final.
 * @param string $order ordena los hashes en asendencia ASC o desendencia
 * DESC segun como se fueron agregando.
 * @return mixed $hashes bool/false -> de error,array hashes -> caso contrario
 * @access publico
 * @link 
 */
 public function hashesBetween($hashInit, $hashEnd, $order = 'ASC') {
  if (!$this->validHash($hashInit) || !$this->validHash($hashEnd) ) {
    return false;
  }
  if ( !in_array(strtoupper($order), array('ASC', 'DESC'))) {
    return false;
  }
  $cmd  = sprintf("git rev-list %s..%s", $hashInit, $hashEnd);
  $hs = explode("\n", `$cmd`);
  $hashes = array();
  foreach ($hs as $h) {
   if ($this->validHash($h) ) {
     $hashes[] = $h;
   }
  }
  $hashes[] = $hashInit;
  if ( strtoupper($order) == 'ASC' ) {
   $hashes = array_reverse($hashes);
  }
  return $hashes;
 }//end hashesBetween

/**
 * Revisa que sea un hash valido(40 caracteres hexadecimales)
 *
 * @param string $hash a revisar
 * @return boolean true en caso de ser valido false en caso contrario
 * @access publico
 * @link
 */
 public function validHash( $hash ) {
  $h = "" . $hash;
  return preg_match('%[0-9a-f]{40}%i', $h)? true : false;
 }//end validHash

/**
 * Regresa el hash del ultimo commit
 *
 * @return string $hash 
 * @access publico
 * @link
 */
 public function lastCommitHash( ) {
   return $this->prevCommitHash();
 }//end lastCommitHash

/**
 * Regresa el hash del commit anterior numero $prevNum
 *
 * @param tipo $parametro1 descripción del párametro 1.
 * @return tipo descripcion de lo que regresa
 * @access publico/privado
 * @link [URL de mayor infor]
 */
 public function prevCommitHash($prevNum =  1) {
  if ( !is_numeric($prevNum) || $prevNum < 1 ) {
    return false;
  }
  $cms = sprintf("git log --pretty=format:\"%s\" -%d | tail -1", '%H', $prevNum);
  return `$cms`;
 }//end prevCommitHash

/**
 * Devuelve el valor(es) del elemento solicitado en $varName
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
 * @param string $parametro1 descripción del párametro 1.
 * @return mixed string en caso que solo exista un valor, arreglo en caso que existan varios, null en caso de error.
 * @access publico
 * @link [URL de mayor infor]
 */
 public static function config($varName) {
  $variables = array();
  preg_match_all(
    '%^' . str_replace('.', '\.', $varName) . '.*=.*$%ismU',
    `git config --list`,
    $variables
  );
  if (!empty($variables[0])) {
   if (count($variables[0]) == 1) {
    return preg_replace('%[^=].*=(.*)$%','\1', $variables[0][0]);
   }
    $variables['data'] = array();
   foreach ($variables[0] as $v) {
     $newData = explode('=', $v);
     $variables['data'][$newData[0]] = $newData[1];
   }
   return $variables['data'];
  }
  return null;
 }//end config

}//end class
