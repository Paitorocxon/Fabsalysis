<?php

Class Fabsalysis{
    function classes_in_namespace($namespace) {
        $namespace .= '\\';
        $myClasses  = array_filter(get_declared_classes(), function($item) use ($namespace) { return substr($item, 0, strlen($namespace)) === $namespace; });
        $theClasses = [];
        foreach ($myClasses AS $class):
              $theParts = explode('\\', $class);
              $theClasses[] = end($theParts);
        endforeach;
        return $theClasses;
  }

    public static function listPHPStructure(){

        $namespaces=array();
        foreach(get_declared_classes() as $name) {
            if(preg_match_all("@[^\\\]+(?=\\\)@iU", $name, $matches)) {
                $matches = $matches[0];
                $parent =&$namespaces;
                while(count($matches)) {
                    $match = array_shift($matches);
                    if(!isset($parent[$match]) && count($matches))
                        $parent[$match] = array();
                    $parent =&$parent[$match];
        
                }
            }
        }
        foreach(get_declared_interfaces() as $interface){
            class_implements($interface);
        }
        foreach(get_declared_classes() as $class){ // Get every class;
            print '<div class="class" style="background-color:#EEE; color: #444; border:2px solid #444; width: 80%; margin: auto; margin-bottom: 200px; border-radius: 2px; box-shadow: 2px 2px 5px #000;"> <h1>Class: ' . $class . '</h1>';
            $R = new ReflectionClass($class);
            foreach($R->getInterfaceNames() as $interfaceName){
                print ' Interface: ' . $interfaceName . '<br>';
            }



            foreach(get_class_methods($class) as $method){
                print '<br/><br/><h3><i>'.$class.'\</i><a name="'.$method.'" href="#'.$method.'">' . $method . '</a>:</h3><div class="method" style="background-color:#444; color:#FFF; margin-bottom:15px; margin-top:20px; border: 1px solid #222; padding: 20px;">Method:<br/><font color="#85e085"><pre>' .  $R->getMethod($method)->getDocComment() . '</pre></font><br/> <font color="#ffcc66">' . $method . '</font><font color="orange">(</font>' ;
                
                $re = new ReflectionMethod($class, $method);

                $params = $re->getParameters();
                $paramcount = 0;
                foreach ($params as $param) {
                    if ($param->isOptional()){
                        print '<font color="gray"><sup>Optional</sup></font> <font color="#E2E82F">$' . $param->getName() . '</font>';
                    } else {
                        print '<font color="#2FE8CE">$' . $param->getName() . '</font>';
                    }
                    if ($paramcount<count($params)-1 && count($params)>1){
                        print ',&nbsp&nbsp';
                    }
                    $paramcount++;
                }
                print '<font color="orange">);</font>';
                if ($re->getReturnType() != null){
                    @print '<br /><br /><font color="#ff8080">returns \'<font color="#ecffb3">' . $re->getReturnType() . '</font>\'</font>';
                }
                print '</div>';
                try {
                    if ($R->getFileName() != false){
                        $cn = $class;
                        $method = $method;
                        
                        $func = new ReflectionMethod($cn, $method);
                        
                        $f = $func->getFileName();
                        $start_line = $func->getStartLine() - 1;
                        $end_line = $func->getEndLine();
                        $length = $end_line - $start_line;
                        
                        $source = file($f);
                        $source = implode('', array_slice($source, 0, count($source)));
                        $source = preg_split("/(\n|\r\n|\r)/", $source);
                        $body = '';
                        for($Ri=$start_line; $Ri<$end_line; $Ri++){
                            $body.="{$source[$Ri]}\n";
                        }
                        ini_set("highlight.comment", "#85e085");
                        ini_set("highlight.default", "#ffcc66");
                        ini_set("highlight.html", "#000");
                        ini_set("highlight.keyword", "#2FE8CE; font-weight: bold");
                        ini_set("highlight.string", "#ffcc66");
                        $highlightedCode = highlight_string('<?php' . "\n" .$body.'?>',true);
                        $highlightedCode = str_replace('(','<font color="#FFA500">(</font>', $highlightedCode);
                        $highlightedCode = str_replace(')','<font color="#FFA500">)</font>', $highlightedCode);
                        $highlightedCode = str_replace('::','<font color="#32a852">::</font>', $highlightedCode);
                        $highlightedCode = str_replace('{','<font color="#FFA500">{</font>', $highlightedCode);
                        $highlightedCode = str_replace('}','<font color="#FFA500">}</font>', $highlightedCode);
                        print '<div class="method" style="background-color:#444; color:#FFF; margin:15px; margin-bottom: 80px; border: 1px solid #222; border-radius: 3px; padding: 7px;">Sourcecode:<br/>' . $highlightedCode . '</div>';
                    }
                } catch (Exception $e) {
                    print $e;
                    die();
                }
            }
            print '</div>';
        }
        return;
    }
}
Fabsalysis::listPHPStructure();