<?php

class GitShell {
 public $script = null;
 public $output = null;
 public $params = null;
/**
 * Lista de opciones soportadas, como valor la ubicacación de los archivos correspondientes a la opcion
 */
protected $_validOptions = array(
        'service' => 'lib/services/',
        'help' => 'lib/tools/Help',
        'hook' => 'hooks/',
        'tool' => 'lib/tools/'
    );
    
 public function __construct($args = array()) {
    $this->output = new CosoleOutput();
    if(count($args)<2){
        $this->_stop('Error: por favor envie mas argumentos',1);
    }
    $this->parseParams(array_slice($args, 1));
    $paramName = $this->params[0]['paramName'];
    $value = $this->camelCase($this->params[0]['value']);
    
     if( array_key_exists($paramName, $this->_validOptions) ){

		#importamos las clases Service y Hook en caso de ser requeridas.
        if( in_array($paramName, array('service', 'hook')) )
            GitShell::import( $this->_validOptions[$paramName]. $this->camelCase($paramName) . '.php');
		
		#cargamos el archivo requerido
        $file = $this->_validOptions[$paramName] . $value . '.php';
        GitShell::import( $file );

		#Inicilizamos la clase y corremos sus callbacks
        $className = $this->camelCase($value . ' ' . $paramName);
        echo 'Iniciando clase '. $className . "\n";
        $this->script = new $className();
        $this->script->initialize();
        $this->script->run(array_slice($this->params, 1));
     }else{
        $this->_stop('Error: Inserto una opción invalidad, favor de verificarla', 1);
     }
   }//end __construct()
    
    public static function import($filename){
          if( DS != '/' )
            $filename = str_replace('/', DS, $filename);
          $filename =  PHPGIT_PATH . DS . $filename;
          if( file_exists( $filename )) {
            include_once $filename;
          } else {
               $output = new CosoleOutput();
               $output->write('<error>Error:</error> Al importar el archivo: <warning>' . $filename .'</warning>' );
               exit( 1 );
          }
    }
 public static function run($argv) {
   $dispatcher = new GitShell($argv);
 }//end run()


    public static function bootstrap(){
        if (!defined('PHPGIT_RUNNING')) {
         if ( strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ) {
          define('OS', 'WIN');
         } else {
           define('OS', 'UNIX');
         }
         define('PHPGIT_RUNNING', 1);
         define('DS', DIRECTORY_SEPARATOR);
         define('PHPGIT_PATH', dirname(__FILE__));
         GitShell::import( 'lib' . DS . 'GitPHP.php');
         GitShell::import( DS . 'lib' . DS . 'tools' . DS .'ConsoleOutput.php');
        }
    }
 protected function _stop($msg='', $status = 0) {
  if($msg != '' )
        $this->output->write("<error>{$msg}</error>\n");
  exit($status);
 }
 /**
  * Descripción de la función
  *
  * @param tipo $parametro1 descripción del párametro 1.
  * @return tipo descripcion de lo que regresa
  * @access publico/privado
  * @link [URL de mayor infor]
  */
 public function parseParams($params) {
     unset($this->params);
     array_walk($params,array(&$this,'_parseParam'));
     #$this->params = $params;
 }
 /**
  * convierte los parametros de la forma
  * --service=ftp ó -service=ftp a array('SERVICE' => 'Ftp')
  *
  * @param tipo $parametro1 descripción del párametro 1.
  * @return tipo descripcion de lo que regresa
  * @access publico/privado
  * @link [URL de mayor infor]
  */
 private function _parseParam(&$param) {
    $aux = split('\=',$param);
    if( count($aux) != 2 )
        $aux[1] = '';
    $aux[0] = str_replace('-','',$aux[0]);
    $this->params[] = array( 'paramName' => $aux[0], 'value' =>$aux[1]);
 }
 
 
 public function camelCase($str){
    $str = str_replace(array('-', '_'), ' ', $str);
    $str = ucwords($str);
    return str_replace(' ','', $str);
 }
}//end class
############################################
GitShell::bootstrap();
echo GitShell::run($argv);
