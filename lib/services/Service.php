<?php
/*
 * Service
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
defined('PHPGIT_RUNNING') || die;
interface ServiceInterface {    
    /**
     * runing a action on service.
     *
     * @param params $array
     * @return boolean Success
     */
    public function run($params=null);
}//end class Service


class ServiceClass extends GitPHP{
    protected $_config = array();
    protected $_base_param = 'null';
    /**
     * Descripción de la función
     *
     * @param tipo $parametro1 descripción del párametro 1.
     * @return tipo descripcion de lo que regresa
     * @access publico/privado
     * @link [URL de mayor infor]
     */
    function initialize () {
        $this->className = get_class($this);
        $this->_base_param = 'php-tools.service.';
        $this->_base_param .= preg_replace('%service$%', '', strtolower($this->className));
        $this->_initConfigParams('', $this->_config, key($this->_config));
        #$this->_base_param
    }
    
    protected function _initConfigParams( $basePath, $data){
        foreach( $data as $k => $v){
            $newBasePath = $basePath . '.' . $k;
            if( is_array( $v) ) {
                $this->_initConfigParams(&$newBasePath,$v);
            } else {
                echo $this->loadVar($newBasePath);
            }
        }
    }
    
    /**
     * Carga un valor por defecto
     *
     * @param tipo $parametro1 descripción del párametro 1.
     * @return tipo descripcion de lo que regresa
     * @access publico/privado
     * @link [URL de mayor infor]
     */
    function loadVar($basePath){
        $value = $this->config( $this->_base_param . $basePath);
        if( !$value )
            return null;
        $query='';
        foreach( explode('.', $basePath) as $e){
            if( strlen($e)>1)
                $query .= '["' . $e .  '"]';
        }
        $exec = '$this->_config'.$query.' =  "'.$value.'";';
        eval($exec);
        return $value;
    }
    /**
     * Descripción de la función
     *
     * @param tipo $parametro1 descripción del párametro 1.
     * @return tipo descripcion de lo que regresa
     * @access publico/privado
     * @link [URL de mayor infor]
     */
    protected function _mergeConfig($config){
        foreach ($config as $param) {
            $key = $param['paramName'];
            if( array_key_exists($key, $this->_config) ){
                $this->_config[ $key ] = $param['value'];
            }//end if
        }
    }
    
    protected function _getVarConfig(){
        
    }
}
