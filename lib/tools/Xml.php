<?php
class  Xml
{
    public $simplexml = null;
    
    public function __construct($file=null)
    {    
        //$this->simplexml = simplexml_load_file($file);
    }

    public function __destruct()
    {    
    }
    /**
     * Convierte el contenido xml a un arreglo asociativo
     *
     * @param string $xmlstring contenido xml
     * @return array arreglo asociativo que representa el contenido
     * @access public
     * @link http://www.php.net/manual/en/book.simplexml.php#105330
     */
    function xml2array($xmlstring) {
        $xml = simplexml_load_string($xmlstring);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }
    
    /**
     * Descripción de la función
     *
     * @param tipo $parametro1 descripción del párametro 1.
     * @return tipo descripcion de lo que regresa
     * @access publico/privado
     * @link [URL de mayor infor]
     */
    function array2XML($array) {
        $json = json_encode($array);
        $serializer = new XML_Serializer();
        $obj = json_decode($json);
        if ($serializer->serialize($obj)) {
            return $serializer->getSerializedData();
        }
        else {
            return null;
        }
    }
    /**
     * Descripción de la función
     *
     * @param tipo $parametro1 descripción del párametro 1.
     * @return boolean susses
     * @access publico/privado
     * @link http://www.kirupa.com/forum/showthread.php?262784-How-to-save-XML-files-with-PHP-to-a-server
     */
    function save($dst){
        //--- load in xml file to edit.  if doesnt exist, create ---
        if( file_exists($dst) && ($handle = @fopen($dst, "w")) ){
            //--- set xml as string to be written ---
            $xmlString = $this->simplexml->asXML();
            return @fwrite($handle, $xmlString);
        }
        return false;
    }
    /**
     * Descripción de la función
     *
     * @param tipo $parametro1 descripción del párametro 1.
     * @return tipo descripcion de lo que regresa
     * @access publico/privado
     * @link [URL de mayor infor]
     */
    function otherSave($dst){
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->simplexml->asXML());
        echo $dom->saveXML($dst);
    }
    
    function removeNode(){
        $data='<data>
    <seg id="A1"/>
    <seg id="A5"/>
    <seg id="A12"/>
    <seg id="A29"/>
    <seg id="A30"/>
    </data>';
    $doc=new SimpleXMLElement($data);
    foreach($doc->seg as $seg)
    {
        if($seg['id'] == 'A12') {
            $dom=dom_import_simplexml($seg);
            $dom->parentNode->removeChild($dom);
        }
    }
    echo $doc->asXml();
    }
    
    /**
     * Una forma de borrar un nodo por medio de xpath
     *
     * @param tipo $parametro1 descripción del párametro 1.
     * @return tipo descripcion de lo que regresa
     * @access publico/privado
     * @link [URL de mayor infor]
     */
    function removeNodeByXpath($query = null){
        if($query==null)
            $query = '//seg[@id="A12"]'; #$query = '/data/seg[@id="A12"]';
        $data='<data>
            <seg id="A1"/>
            <seg id="A5"/>
            <seg id="A12"/>
            <seg id="A29"/>
            <seg id="A30"/>
        </data>';
        $doc=new SimpleXMLElement($data);
        $seg=$doc->xpath($query);
        if (count($seg)>=1) {
            $dom=dom_import_simplexml($seg[0]);
            $dom->parentNode->removeChild($dom);
        }
        echo $doc->asXml();
    }
}
$xml = new Xml;
$xml->array2XML($_SERVER);
