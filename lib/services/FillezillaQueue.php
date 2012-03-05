#!/usr/bin/env php
<?php
/*
 * FilezillaQueque - una extension de GitPHP que nos ayuda en el manejo de la cola de archivos del filezilla.
 * 
 * Ejemplo de uso
 * -----------------
 * $gitPhpFillezillaQueue = new FillezillaQueue();
 * $last_commit = $gitPhpFillezillaQueue->lastCommitHash();
 * $prev_commit = $gitPhpFillezillaQueue->prevCommitHash(6);
 * 
 * $files =  $gitPhpFillezillaQueue->updateFiles( $prev_commit, $last_commit);
 * foreach ($files as $k=>$f)
 * {
 * 	echo ($k+1).' '.$f['LocalFile']."\n";
 * }
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
 
class FillezillaQueue extends Service{
	public $files_queue = array();
	public $hash1 = '';
	public $hash2 = '';
	protected static $config;
	
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
		if (empty(self::$config))
		{
			self::$config['localpath'] = $this->getConfigParam('filezilla.local-path');
			self::$config['dir-export'] = $this->getConfigParam('filezilla.dir-export');
			self::$config['remote-path'] = $this->_remotePathFormat($this->getConfigParam('filezilla.remote-path'), true);
		}
	}
	/* public function __get($name) {} */
	
	/**
	 * Descripción de la función
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link
	 */
	####################################################
	function updateFiles($hash1, $hash2) {
		$this->files_queue = array();
		
		#obtenemos la lista de los archivos modificados
		$files = $this->filesBetweenCommits($hash1, $hash2);
		
		foreach($files as $file){
			if( !preg_match("%^".self::$config['dir-export']."%", $file))
				continue;
			$file = preg_replace("%^".self::$config['dir-export'].DS."%",'',$file);
			$file_path = self::$config['localpath'].DS.self::$config['dir-export'].DS.$file;
			
			$file_data=array(
				'LocalFile' => $file_path,
                'RemoteFile' => preg_replace('%(.*)/([^/]*)%i','\2',$file), #$file,
                'RemotePath' => self::$config['remote-path'] . $this->_remotePathFormat(preg_replace('%(.*)/([^/]*)%i','\1',$file)),
                #'Download' => 0,
                'Size' => 0,
                'TransferMode' => 0,
                'Action' => 'update',
			);
			if( file_exists($file_path) ){
				$file_data['Size'] = filesize($file_path);
			}else
				$file_data['Action'] = 'delete';
			$this->files_queue[] = $file_data;
		}
		return $this->files_queue;
	}
	/**
	 * Descripción de la función
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	private function _remotePathFormat($remotePath = '', $init = false) {
		$parts = explode(DS, $remotePath);
		$out = '';
		foreach($parts as $dir) 
		{
			if(strlen($dir))
				$out .= ' '.strlen($dir). ' ' . $dir;
		}
		if($init)
			$out = '1 0'.$out;
		return $out;
	}
}
