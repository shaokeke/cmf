<?php 

	include_once('lib/CompoundFile.php') ;

	
    class MoticIndex{

        const NAME_PROPERTY = "Property";
        const NAME_DSIO = "DSI0";
        const NAME_MoticDigitalSlideImage = "MoticDigitalSlideImage";
        private $cf;

    	public function __construct($slidePath)
        {
        	$fp = fopen( $slidePath, 'r' );
        	$this->cf = new CompoundFile( $fp );
        	//先获取属性
			//$this->GetProperty();

			//获取层信息
			$this->GetLayerInfo();

			//获取Tile信息
			$this->GetTileInfo();

			// 获取Label
            $this->GetLabel();

            // 获取AssociateImage
            $this->GetAssociateImage();

        }
        public function GetProperty()
        {
        	
        	$stream  = $this->cf->getStream( self::NAME_PROPERTY );

        	//使xml可以正常读取
        	$xmlcode = str_replace( chr(hexdec('00')) ,'' ,$stream ) ;

        	var_dump( $this->parseXml($xmlcode) );exit;

        	if( $stream==null )
        	{
        		throw new Exception('[麦克奥迪]无法获取切片的属性');
        	}

        }
        public function GetLayerInfo()
        {
        	$stream  = $this->cf->getDirectory( '1.000000' );
        	//$stream  = $this->cf->getStream( "DSI0" );
        	var_dump($stream);exit;
        }
        public function GetTileInfo()
        {
        	
        }
        public function GetLabel()
        {
        	
        }
        public function GetAssociateImage()
        {
        	
        }
        public function parseXml($xmldata)
        {
            $xmlarr = explode(  '<?xml version="1.0" encoding="unicode" ?>' , $xmldata ) ;

           
            $xmlv = array() ;

            foreach( $xmlarr as $xmlstr ){

                if( ! empty( $xmlstr ) ){

                    $xml = simplexml_load_string ($xmlstr);
                    $xmlobj =  $this->object_array($xml) ;

                    foreach( $xmlobj as $key=> $val){

                        if( isset( $val['@attributes'] ) ){
                            $xmlv[ $key ] = $val['@attributes']['value'] ;
                        }else{

                            if( is_array( $val ) ){

                                foreach( $val as $kk=>$vv ){
                                    if( isset( $vv['@attributes'] ) ){
                                        $xmlv[ $key ][$kk] = $vv['@attributes']['value'] ;
                                    }else{
                                        if( is_array( $vv ) ){

                                            foreach( $vv as $kkk=>$vvv ){
                                                if( isset( $vvv['@attributes'] ) ){
                                                    $xmlv[ $key ][$kk][$kkk] = $vvv['@attributes']['value'] ;
                                                }else{


                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $xmlv ;

        }

	    public function object_array($array) {  
	         if( is_object($array) ) {  
	              $array = (array)$array;  
	         } 
	         
	         if(is_array($array)) {  
	             foreach($array as $key=>$value) {  
	                     $array[$key] = $this->object_array($value);
	             }  
	         }  
	         return $array;  
	    }
        
       
    }

    $slidePath = '1.mds' ;
    $cs = new MoticIndex($slidePath);
    //$cs->GetProperty;
    //$cs->GetLayerInfo;
 ?>