<?php
    /**
     * 麦考奥迪扫描仪  切片文件分析处理 
     * Author: 王丹龙 2017.4.8
     */
     
     function infosave( $sid , $msg ){
        $data = array(
                'utime' => app::getnowtime() , 
               'fx_msg' => substr( $msg ,0 , 500 ) , 
                     ) ; 
         $res = app::dbsave('consult_section',$data ,'up' ,' id='.$sid) ; 
        return $msg ; 
     }
//--------------------------
     function mds( $sid , $secfile ,$imgdir ,$up_size) { 
        
        if( ! file_exists($secfile) ){
             exit( infosave( $sid , '没有找到切片文件：'.$mdsfn ) ) ; 
        }
   //----------------------------------------------
           $run_start_time = time() ;
            set_time_limit(0); 
            
       
          $topflg = chr( hexdec('ff') ).chr( hexdec('d8') ).chr( hexdec('ff') ).chr( hexdec('e0') ) ;  //jpg文件开头标记
          $endflg = chr( hexdec('ff') ).chr( hexdec('d9') ) ;  //jpg文件结尾标记

          $xmlflg = chr( hexdec('3c') ).chr( hexdec('00') ).chr( hexdec('3f') ).chr( hexdec('00') )
                   .chr( hexdec('78') ).chr( hexdec('00') ).chr( hexdec('6d') ).chr( hexdec('00') )
                   .chr( hexdec('6c') ).chr( hexdec('00') ) ;  //xml标记
                   
         $rootflg = chr( hexdec('52') ).chr( hexdec('00') ).chr( hexdec('6f') ).chr( hexdec('00') )
                   .chr( hexdec('6f') ).chr( hexdec('00') ).chr( hexdec('74') ).chr( hexdec('00') ) 
                   .chr( hexdec('20') ).chr( hexdec('00') ).chr( hexdec('45') ).chr( hexdec('00') ) 
                   .chr( hexdec('6e') ).chr( hexdec('00') ).chr( hexdec('74') ).chr( hexdec('00') ) 
                   .chr( hexdec('72') ).chr( hexdec('00') ).chr( hexdec('79') ).chr( hexdec('00') )  ;  //xml标记
                   
        $moticflg = chr( hexdec('4d') ).chr( hexdec('00') ).chr( hexdec('6f') ).chr( hexdec('00') )
                   .chr( hexdec('74') ).chr( hexdec('00') ).chr( hexdec('69') ).chr( hexdec('00') ) 
                   .chr( hexdec('63') ).chr( hexdec('00') ).chr( hexdec('44') ).chr( hexdec('00') ) 
                   .chr( hexdec('69') ).chr( hexdec('00') ).chr( hexdec('67') ).chr( hexdec('00') ) 
                   .chr( hexdec('69') ).chr( hexdec('00') ).chr( hexdec('74') ).chr( hexdec('00') ) 
                   .chr( hexdec('61') ).chr( hexdec('00') ).chr( hexdec('6c') ).chr( hexdec('00') ) 
                   .chr( hexdec('53') ).chr( hexdec('00') ).chr( hexdec('6c') ).chr( hexdec('00') ) 
                   .chr( hexdec('69') ).chr( hexdec('00') ).chr( hexdec('64') ).chr( hexdec('00') ) 
                   .chr( hexdec('65') ).chr( hexdec('00') ) ;  //xml标记
                   
        $macroflg = chr( hexdec('4d') ).chr( hexdec('00') ).chr( hexdec('61') ).chr( hexdec('00') )
                   .chr( hexdec('63') ).chr( hexdec('00') ).chr( hexdec('72') ).chr( hexdec('00') ) 
                   .chr( hexdec('6f') ).chr( hexdec('00') ) ;  //xml标记
                   
        $labelflg = chr( hexdec('4c') ).chr( hexdec('00') ).chr( hexdec('61') ).chr( hexdec('00') )
                   .chr( hexdec('62') ).chr( hexdec('00') ).chr( hexdec('65') ).chr( hexdec('00') ) 
                   .chr( hexdec('6c') ).chr( hexdec('00') ) ;  //xml标记
                   
   //---------------------  
         $rsize   = 4 * 1024 *1024 ; //每读取数据块大小 4M
         $fsize   = filesize( $secfile ) ;  
         $for_num =  ceil( $fsize / $rsize ) ; //循环次数         
         $handle  = fopen( $secfile ,  "rb"  ) ; //打开文件句柄    // header('Content-type: text/plain;charset=utf-8');

           $contents ='' ;

        for( $fii=$for_num;$fii>=0;$fii--){
            
           // echo ' $fii-->'.$fii.'  '.$for_num ;
            
                    $box_s = $fii * $rsize ; 
                $fseek_res = fseek( $handle ,$box_s ) ; 
            if( $fseek_res == 0 ) { 
                 $contents = fread( $handle ,$rsize ).$contents ;       //  $eval = not_null( $eval ) ; 
            }else{
                 exit('not fseek') ; 
            }
          
                      $is_ok = 0 ; 
                  $indexcode = '' ;  
                    $xmlcode = '' ; 
                    $xmlpos  = strpos( $contents , $xmlflg ) ;   //echo $xmlpos.'-->>> $xmlflg '  ; 
                if( $xmlpos !== false ){ 
                
                        $rootpos = strpos( $contents ,$rootflg ,$xmlpos ) ; 
                    if( $rootpos !== false ){   //echo $rootpos.' -->>> $rootflg' ; 
                        
                             $moticpos = strpos( $contents ,$moticflg ,$rootpos ) ; 
                         if( $moticpos !== false ){    //echo $moticpos.' ---->>> $moticflg' ; 
                            
                             $xmllong = $rootpos - $xmlpos ; 
                             $xmlval  = substr( $contents ,$xmlpos , $xmllong ) ; //echo 'xml adds '.$xmlpos.' -> '.$xmllong ; 
                             $toppos  = strpos( $xmlval ,$topflg ) ; 
                         if( $toppos !== false ){
                            
                                 $endpos = strpos( $xmlval ,$endflg ) ;
                             if( $endpos !== false ){
                                 $xmlval = substr( $xmlval ,0 , $toppos ).substr( $xmlval , $endpos )  ;
                             }else{
                                
                                
                             }
                         }
                      
                         $xmlcode = str_replace( chr(hexdec('00')) ,'' ,$xmlval ) ;
                       $indexcode = substr( $contents ,$rootpos , $moticpos - $rootpos  ) ; 
                           $is_ok = 1 ; 
                           break ;             
                    } 
                    
                }
        } //查找标记
      } //分块提取数据 
  //----------------------------------------------------      
      if( $is_ok==0 ){
        app::message_show( $secfile.'文件格式不正确！没有找到关键数据！' ) ;  exit; 
      }
  //------------
                        $ini = xmlfx($xmlcode) ;  
           if( is_array($ini) && isset( $ini['ImageMatrix'] )  ){
            
           }else{
             echo ' 切片配置参数解析不正确！' ;
             exit ; 
           }

             $zzoom = $ini['ScanObjective'] ; //总倍数  
                $iw = $ini['ImageMatrix']['CellWidth'] ; 
                $ih = $ini['ImageMatrix']['CellHeight'] ;
      //------------------------------------------  //  '0' => array(  'w' => '12088' ,  'h' => '18382' ,  'ws' => '48' ,  'hs' => '72' ,  'num' => '3456' ,  'zoom' => '40' ,  'subdir' => './upload/201703/10/1444/40' ,  ) , 
                    $aii = 0 ;  
                $img_zs  = 0 ;    
                $img_num = 0 ;  
                $img_info='' ;   
                    
                foreach( $ini['ImageMatrix'] as $ikey=>$ival ) {
                    
                     if( is_array($ival) && substr( $ikey ,0,5 )=='Layer' ){
   
                               $zoom    = floatval( $ival['Scale'] ) * $zzoom ; 
                               $zoomurl = $imgdir.'/'.$zoom  ; 
                               $img_num = $ival['Rows'] * $ival['Cols'] ; 
                               $img_zs  = $img_zs+$img_num ;
                               
                           if( $img_num ==1 ){
                                 break ; 
                           }else{
                            
    $img_info = $img_info."
   '$aii' => array(  'w' => '".$ival['Cols']*$iw."' ,  'h' => '".$ival['Rows']*$ih."' ,  'ws' => '".$ival['Cols']."' ,  'hs' => '".$ival['Rows']."' ,  'num' => '".$img_num."' ,  'zoom' => '".$zoom."' ,  'subdir' => '".$zoomurl."' ,  ) ,  
   ";                       
                               $aii++ ;
                           }
                     }
                }
                        
         $img_info = $img_info.'   ) ; 
           ' ; 
   //-----------------------------------------------------
           $info = '<?php
           ' ; 
                 $info = $info.'$kfb = array( 
                          "ver" => 1.0 
                      , "count" => '.$img_zs.'   
                          , "w" => '.$ini['ImageMatrix']['Width'].'   
                           ,"h" => '.$ini['ImageMatrix']['Height'].'  
                         ,"mpp" => 0.50010301 
                      , "fsize" => '.$fsize.'  
                        ,"zoom" => '.floatval($ini['ImageMatrix']['Layer0']['Scale']) .'   
                    ,"infolist" => 0 
               , "infolist_end" => 0 
               , "iw" => '.$iw.'
               , "ih" => '.$ih.'
               
                ) ; 
               
                         $dirinfo = array(
                    ' . $img_info ;  
   //--------------------------------------------------   
                        $ini_path = $imgdir.'/info.php' ; 
     file_put_contents( $ini_path , $info ) ;    //echo ' $img_num-->'.$img_num.'  $img_count-->'.$img_count ;   print_r( $ini ) ;   
   //===============索引计算==============
     if( ! file_exists($ini_path) ){
           echo ' 没有创建文件：'.$ini_path ; exit ; 
     }
         require( $imgdir.'/info.php' ) ; 
   //   
     if( ! isset( $dirinfo ) ) {  
           echo ' 切片配置文件错误！' ; exit ; 
     }
//=========================================================================
          $rlong  = 64*1024 ;  
          $isize  = 128 ; //每读取数据块大小 4M    
          $ilong  = strlen( $indexcode ) ;  
         $for_num =   ceil( $ilong / $isize ) ; //循环次数         

       $front_inx = '' ;  //前半部分定位 
     $next_top_py = 0 ; 
       
          $a_ibox = array() ; 
    
          $iboxii = 0 ; 
       $img_start = 0 ;
        
             $izs = 0 ; 
             $inx ='' ;
  //----------------------------------------------------        
    for( $xii=0;$xii<$for_num;$xii++){
  //====================================================
              $d6 = '' ; 
              $d7 = '' ; 
              $d8 = '' ; 
             $err = '' ; 
     
          $istart = $xii*$isize ;
             $inx = substr( $indexcode ,$istart ,$isize ) ; 
             $izs++;
   //--------------
          $payi_1 = substr( $inx , 68 ,4  ) ;
          $payi_2 = substr( $inx , 72 ,4  ) ;
          $adds   = substr( $inx , 64 ,16 ) ;
          $long   = substr( $inx ,116 ,8  ) ;
     //============================================      
           $iname = substr( $inx ,0   ,19 ) ;
           $imgl  = substr( $inx ,120 , 4 ) ;
           
         $type    = substr( $inx ,64 ,1 ) ;
         $typenum =    ord( $type ) ; 
     //============================================   
        if( $typenum ==20 || $typenum ==12 ){ //图块索引处理
        
            if( $iboxii == 0 ) {
                $img_start = 512 ; 
            }else{
                $img_start = $img_start + $img_long + $next_top_py ;   
            }
            
                $stat = '' ; 
            $img_long = str_dec( $imgl ) ; 
    //====================================================================
            if( fseek( $handle , $img_start ) == 0 ) {
            
                           $idata = fread( $handle , $img_long ) ; //   $panyi = not_null($idata) ; //字串第几个不为00 
               if( substr( $idata ,0 ,4 ) == $topflg ){
                    
                       $py_top  =  strpos( $idata ,$topflg ,4 ) ;  
                   if( $py_top !== false ){
                    
                            $stat = 'front' ;
                             $two = $img_start+$py_top - $fsize ; 
                       $front_inx = $img_start.'_'.$py_top.'_'.$two ;  //前半部分定位   
                       $front_long= $py_top ;                 //echo ' --->front '. $front_inx ; 
                       
                       $img_start = $img_start + $py_top ; 
                   }  
               }else{
                    //有可能是 下半部   $err = $err.'图片头部不正确！' ; 
                    
                    if( substr( $idata ,0 ,10 ) == $xmlflg ){    // xml标记
                    
                             // echo  ' ==== xml ------ ' ; 
                        
                           $py_top  =  strpos( $idata ,$topflg ,4 ) ;  
                       if( $py_top !== false ){
                           $img_start = $img_start + $py_top ; 
                       }else{
                        
                           $err = $err.'xml块 ffd8定位失效！'.$img_start ; 
                       }
                    }else{
                        $stat = 'back'  ;     // echo ' ----> back ' ;    
                        $img_long = $img_long - $front_long ;          
                    }
                    
               }
                   
            }else{
                   $err = $err.'$img_start 定位失效！'.$img_start ; 
            } 
    //-------------------------------------------------------------------
                $top_spy = $img_start % 16 ; 
                
                $img_start = $img_start - $top_spy ; 
                
                $endadds = $img_start + $img_long - 2 ;  //echo ' ===== '.$top_spy.' $img_start->'.$img_start.' '.$img_long.' $endadds-->'.$endadds ; 
               
            if( fseek( $handle , $endadds ) == 0 ) {
                
                               $idata = fread( $handle , 2048 ) ; 
                   if( substr( $idata , 0 , 2 ) == $endflg ){ 
                           
                           $two  = $img_start + $img_long - $fsize ; 
                           $iboxdata = $img_start.'_'.$img_long.'_'.$two ;  

                       if( $stat == 'back' ){
                           $iboxdata = $front_inx.'_'.$iboxdata ;    
                       }
                        $next_top_py = not_null( substr( $idata ,2 ) ) ; 
                      //------------------------------------------------- 
                        $w_h  = str_replace( chr(hexdec('00')) ,'' ,$iname ) ;   // $wharr = explode('_',$w_h) ; $wharr[1].'_'.$wharr[0]
                        $iaii = $w_h.'_'.$iboxii;
                      
                     //  $d6 = $stat.' 前导零数-->'.$next_top_py ;
                     //  $d7 = $iaii; 
                     //  $d8 = $iboxdata ; 
          //-------------------------------------------------                       
                       if( $typenum ==12 ){ //图块索引处理  标签 与 缩图 
                            if( substr( $iname,0,10) == $macroflg ){ //缩略图
                                $res = app::getimgsave( $secfile ,$iboxdata ,$imgdir.'/preview.jpg' ) ;  //echo ' ---> macro  '.$imgdir.'/preview.jpg' ; 
                            }
                            if( substr( $iname,0,10) == $labelflg ){ //标签图
                                $res = app::getimgsave( $secfile ,$iboxdata ,$imgdir.'/card.jpg'    ) ;  //echo ' ---> label  '.$imgdir.'/card.jpg' ; 
                            }
                       }else{
                               $a_ibox[$w_h][] = $iboxdata ;
                       }
          //-------------------------------------------------                        
                   }else{
                      $err = $err.'图片尾部不正确！' ; 
                   }
            }else{
                      $err = $err.'$img_start 定位失效！'.$img_start ; 
            } 
     //====================================================================
            $iboxii++ ; 
        }  //图块处理
     //----------------   
     /*   
        echo '<br/>#'.$izs.' iname--> '.str_replace( chr(hexdec('00')) ,'' ,$iname ).'   ' ; 
            $sv = array( 
            'section_id' => $sid , 
            'hex_data_1' => hexstr( $iname ) , 
            'hex_data_2' => hexstr( $adds  ) , 
            'hex_data_3' => hexstr( $long  ) , 
            'd1'=> str_replace( chr(hexdec('00')) ,'' ,$iname )  , 
            'd2'=> hexstr( $type  )  , 
            'd3'=> $typenum ,
            'd4'=> $img_start ,   
            'd5'=> $img_long  ,   
            'd6'=> $d6  ,   
            'd7'=> $d7  ,   
            'd8'=> $d8  ,   
            'd9'=> $err  ,   
                          );
            app::dbsave('fx_index' , $sv ,'ins' ) ; 
      */     
  //-------------            
    } //索引扫描结束 
       fclose( $handle ) ;    //关闭文件名柄，释放资源 
  //====================================================
         $section_ver = '' ;
         $zii = count( $a_ibox[ '0000_0000' ] ) -1  ;
         $iboxdata =        $a_ibox[ '0000_0000' ][$zii] ;
         $res = app::getimgsave( $secfile ,$iboxdata ,$imgdir.'/map.jpg'    ) ;  //echo ' ---> label  '.$imgdir.'/map.jpg' ;
         //====================================================
        ksort( $a_ibox ) ;   header( 'Content-Type :text/plain' ) ; //  print_r( $a_ibox ) ; 

               $zii = 0 ; 

      foreach( $dirinfo as $dkey => $dval  ){
           //---------新建子目录----------- 
            if (!file_exists( $dval['subdir'] ) ) {
                if(  ! mkdir( $dval['subdir'] , 0777 ,true ) ){
                        exit( infosave( $sid ,  '错误代码:601 新建子目录失败！' ) ) ; 
                }
            }
        
                $ws = $dval['ws'] ; 
                $hs = $dval['hs'] ;
            
            for($wii=0;$wii<$ws;$wii++){
                    $liststr = '' ; 
                for($hii=0;$hii<$hs;$hii++){
                    $aiiname = sprintf("%04d", $hii).'_'.sprintf("%04d", $wii) ;
                    if($iw==512&&$ih==512){
                        if ($wii == 0 || $wii == 1) {
                            $bhval = $a_ibox[$aiiname][$zii];
                        } else {
                            $c = count($a_ibox[$aiiname]) - 1;
                            $bhval = $a_ibox[$aiiname][$c];
                            unset($a_ibox[$aiiname][$c]);
                        }
                     }else{
                        $bhval = $a_ibox[ $aiiname ][$zii] ;
                    }
                    $liststr = $liststr . " $hii => '$bhval' , " ; 
                    
                   // echo '
                   // '.$aiiname.' --> '.$zii.'  '.$bhval ;
                }
                
    $liststr = '<?php 
     $imgbox = array( 
       '.$liststr . '
       ) ;
    ' ; 
                                  $newinfofile = $dval['subdir'].'/'.$wii .'.php' ;  
               file_put_contents( $newinfofile , $liststr ) ;  
            }
               $zii++ ; 
      }  
 //======================================================

 //------------------------
   $cardfile = $imgdir.'/card.jpg' ; 
 
   if( ! file_exists( $cardfile ) ) $cardfile = './upload/null.jpg' ; 

        $data = array(
                   'section_status' =>     3   , 
                   'section_img'    => $imgdir.'/map.jpg'  ,  //导航图  ,
                   'section_label'  => $cardfile , 
                   'section_type'   => 'mds' , 
                   'section_ver'    => $section_ver , 
                            'utime' => app::getnowtime() , 
                           'fx_msg' => 'ok!' , 
                   ) ; 
                 
            $res = app::dbsave('consult_section',$data ,'up' ,' id='.$sid) ; 
        if( $res ){
            
        }else{
            exit( '错误代码:402 数据保存失败！' ) ;  
        } 
      //运行时间 秒
        $run_time = time() - $run_start_time ;   // echo ' runt time :'. $run_time .'sec';   
      //     
        echo 'ok!' ;  

 //------------------------------       
     }  //解析完成发      
 //========================================================================              
  //----------------------------------------    
     function  xmlfx( $xmldata ){
        
        $xmlarr = explode(  '<?xml version="1.0" encoding="unicode" ?>' , $xmldata ) ; 
        
                 $xmlv = array() ;  
        
        foreach( $xmlarr as $xmlstr ){
            
             if( ! empty( $xmlstr ) ){
                
                    $xml = simplexml_load_string ($xmlstr);
                    $xmlobj =  object_array($xml) ; 
                    
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
  //------------------------------------
      function object_array($array) {  
         if( is_object($array) ) {  
              $array = (array)$array;  
         } 
         
         if(is_array($array)) {  
             foreach($array as $key=>$value) {  
                     $array[$key] = object_array($value);
             }  
         }  
         return $array;  
    }     
  //-------------------------------------
          
    /**
     *  xml 获取
     */
       function getxml( $eval ,$xmlm ){
        
          $topflg = chr( hexdec('ff') ).chr( hexdec('d8') ).chr( hexdec('ff') ).chr( hexdec('e0') ) ;  //jpg文件开头标记
          $endflg = chr( hexdec('ff') ).chr( hexdec('d9') ) ;  //jpg文件结尾标记

          $xmlflg = chr( hexdec('3c') ).chr( hexdec('00') ).chr( hexdec('3f') ).chr( hexdec('00') )
                   .chr( hexdec('78') ).chr( hexdec('00') ).chr( hexdec('6d') ).chr( hexdec('00') )
                   .chr( hexdec('6c') ).chr( hexdec('00') ) ;  //xml标记
                   
         $rootflg = chr( hexdec('52') ).chr( hexdec('00') ).chr( hexdec('6f') ).chr( hexdec('00') )
                   .chr( hexdec('6f') ).chr( hexdec('00') ).chr( hexdec('74') ).chr( hexdec('00') ) ;  //xml标记
        
        
                       $isok = 0  ;
                    $xmlcode = '' ; 
                    $xmlpos  = strpos( $eval , $xmlflg ) ; 
                if( $xmlpos !== false ){   //  echo ' ---->>> $xmlflg' ; 
                        $rootpos = strpos( $eval ,$rootflg ,$xmlpos ) ; 
                    if( $rootpos === false ){
                             $rootpos = strpos( $eval ,$topflg ,$xmlpos ) ; 
                         if( $rootpos === false ){
                             $rootpos = strlen( $eval ) ;  
                             $isok=1 ;
                         }else{   
                             $isok=1 ;  // echo ' ---->>> $topflg' ; 
                         }                        
                    }else{    
                             $isok=1 ;  // echo ' ---->>> $rootflg' ; 
                    }   
                    
                    if( $isok==1 ){
                        $xml = substr( $eval ,0 , $rootpos ) ; 
                        $xmlcode = str_replace( chr(hexdec('00')) ,'' ,$xml ) ;
                      //  app::dbsave('fx_section',array('xml'=>$xmlcode  ) ,'up' ,'id='.$sid ) ; 
                    }
                }
           return $xmlm.$xmlcode ; 
      }
 
    /**
     *  not_null 
     */
       function not_null( $xval ){ 
        
             $xnull = chr(hexdec('00')) ; 
             $xlen  = strlen( $xval ) ; 
                
                for($xii=0;$xii<$xlen;$xii++){
                   if( substr( $xval ,$xii ,1 ) <> $xnull ){ 
                       break ; 
                   }
                }
             return $xii ;    
     }
   //-----------------四字节字串二进级转数值（十进制）---------------------  
     function  str_dec( $bincode ){
        
            $blong = strlen( $bincode ) ; 
               
             $bin1 = ord( substr( $bincode ,3 ,1 ) )  ; 
             $bin2 = ord( substr( $bincode ,2 ,1 ) )  ;  
             $bin3 = ord( substr( $bincode ,1 ,1 ) )  ; 
             $bin4 = ord( substr( $bincode ,0 ,1 ) )  ; 
            
             $vnum = $bin1*16777216 + $bin2*65536 + $bin3*256 + $bin4 ; //四字节值 
        
      return $vnum ; 
     }
 //--------------------------------       
