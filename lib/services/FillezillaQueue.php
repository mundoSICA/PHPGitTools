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
defined('PHPGIT_RUNNING') or die;
 
class FillezillaQueue extends Service{
	public $files_queue = array();
	public $hash1 = '';
	public $hash2 = '';
	
	/**
	 * Descripción de la función
	 *
	 * @param tipo $parametro1 descripción del párametro 1.
	 * @return tipo descripcion de lo que regresa
	 * @access publico/privado
	 * @link [URL de mayor infor]
	 */
	function __construct($args = null) {
		parent::__construct($args);
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
			if( !preg_match("%^".GitPHP::config('filezilla.dir-export')."%", $file))
				continue;
			$file = preg_replace("%^".GitPHP::config('filezilla.dir-export').DS."%",'',$file);
			$file_path = GitPHP::config('filezilla.localpath').DS.GitPHP::config('filezilla.dir-export').DS.$file;
			
			$file_data=array(
				'LocalFile' => $file_path,
                'RemoteFile' => preg_replace('%(.*)/([^/]*)%i','\2',$file), #$file,
                'RemotePath' => GitPHP::config('filezilla.remote-path') . $this->_remotePathFormat(preg_replace('%(.*)/([^/]*)%i','\1',$file)),
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
