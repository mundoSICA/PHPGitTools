<?php
/*
FilezillaQueque - una extension de GitPHP que nos ayuda en el manejo
de la cola de archivos del filezilla.
 *
Ejemplo de uso
-----------------
$gitPhpFillezillaQueue = new FillezillaQueue();
$last_commit = $gitPhpFillezillaQueue->lastCommitHash();
$prev_commit = $gitPhpFillezillaQueue->prevCommitHash(6);
 *
$files =  $gitPhpFillezillaQueue->updateFiles( $prev_commit, $last_commit);
foreach ($files as $k=>$f)
{
  echo ($k+1).' '.$f['LocalFile']."\n";
}
 *
Copyright 2012 fitorec <programacion@mundosica.com>
 *
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
 *
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
 *
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
MA 02110-1301, USA.
 */

#defined('PHPGIT_RUNNING') || die;

class FillezillaQueue extends GitPHP
#class FillezillaQueue extends ServiceClass implements ServiceInterface {
{
  public $filesQueue = array( );

  public $hashInit;

  public $hashEnd;
/**
Descripción de la función
 *
@param tipo $parametro1 descripción del párametro 1.
@return tipo descripcion de lo que regresa
@access publico/privado
@link [URL de mayor infor]
 */

  public function __construct ( $args = null )
  {
    parent::__construct($args);
  }

/**
Descripción de la función
 *
@param tipo $parametro1 descripción del párametro 1.
@return tipo descripcion de lo que regresa
@access publico/privado
@link
 */

  public function updateFiles ($hashInit, $hashEnd)
  {
    $this->filesQueue = array();
    #obtenemos la lista de los archivos modificados
    $files = $this->filesBetweenCommits($hashInit, $hashEnd);
    $basePath = GitPHP::config('filezilla.localpath') . DS;
    $basePath .= GitPHP::config('filezilla.dir-export') . DS;
    foreach ($files as $file) {
      $regex = "%^" . GitPHP::config('filezilla.dir-export') . "%";
      if (!preg_match($regex, $file)) {
        continue;
      }
      $regex = "%^" . GitPHP::config('filezilla.dir-export') . DS . "%";
      $file = preg_replace($regex, '', $file);
      $file = basename($file);
      $filePath = $basePath . $file;
      $remotePath = GitPHP::config('filezilla.remote-path');
      $remotePath = preg_replace('%(.*)/([^/]*)%i', '\1', $file);
      $remotePath .= $this->__remotePathFormat($remotePath);
      $fileData = array(
        'LocalFile' => $filePath,
                'RemoteFile' => preg_replace('%(.*)/([^/]*)%i', '\2', $file),
                'RemotePath' => $remotePath,
                # <!-- aqui va el dowload con valor a cero 'Download' => 0, -->
                'Size' => 0,
                'TransferMode' => 0,
                'Action' => 'update',
      );
      if ( file_exists($filePath) ) {
        $fileData['Size'] = filesize($filePath);
      } else {
        $fileData['Action'] = 'delete';
      }
      $this->filesQueue[] = $fileData;
    }
    return $this->filesQueue;
  }
/**
Da formato para el FillezillaQueue.xml de un directorio remoto donde, p.e.
 *
/public_html/img/
Se convierte en
0 1 11 public_html 3 img
El formato es el siguiente:
 *
0 1 <-indica inicio
11 public_html -  11 es la longitud de public_html
3 img <- 3 es la longitud de img
 *
@param tipo $parametro1 descripción del párametro 1.
@return tipo descripcion de lo que regresa
@access publico/privado
@link [URL de mayor infor]
 */
  private function __remotePathFormat($remotePath = '', $init = false)
  {
    $parts = explode(DS, $remotePath);
    $out = '';
    foreach ($parts as $dir) {
      if (strlen($dir)) {
        $out .= ' ' . strlen($dir) . ' ' . $dir;
      }
    }
    if ($init) {
      $out = '1 0' . $out;
    }
  return $out;
  }
}
#-----------------------------------------------------------------------------------------
echo 'Hola mundo!'."\n";
echo "trabajando desde el directorio\n";
echo getcwd(). "\n";

$gitPhpFillezillaQueue = new FillezillaQueue();
$last_commit = $gitPhpFillezillaQueue->lastCommitHash();
$prev_commit = $gitPhpFillezillaQueue->prevCommitHash(6);
$files =  $gitPhpFillezillaQueue->updateFiles( $prev_commit, $last_commit);
foreach ($files as $k=>$f)
{
  echo ($k+1).' '.$f['LocalFile']."\n";
}
