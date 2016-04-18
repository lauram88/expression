<?php
class Calculate_expression{

    var $bool_values = array("T"=>true, "F"=>false);
    public function __construct() {

    }

    public function calc_expr($expr){

        echo '<pre>+++++++';
            print_r($expr);
        echo '</pre>++++++';



        $result = $this->calculate_one_statement("T", '&', 'F');
        var_dump($result );
        die();

        //$pattern = '/^()/';
        //$pattern = '/\((.*?)\)/si';
        //$pattern = '/\(+(.*?)\)/';
        //$pattern  = '/\(+(.*?)\)/';
        //$pattern  = '/(?<=\()(.+)(?=\))/is';
        $pattern = '#\(((?>[^()]+)|(?R))*\)#x';
        preg_match($pattern, $expr, $matches);
        echo '<pre>+++++++';
        print_r($matches);
        echo '</pre>++++++';die();

        echo $expr;

        preg_match($pattern, $expr, $matches, PREG_OFFSET_CAPTURE);
        echo '<pre>';
        print_r($matches);
        echo '</pre>';
        $i = 0;
        while (is_string($expr) && (strlen($expr)>1)){
                $i++;
                $expr = $this->recursive_match($expr, 0);
                if ($i++>20){echo 'bucla inifinita';die();}
        }

        echo "</br>";
        echo "pauza";
        echo "</br>";
        var_dump($result);
        echo 'gata'; die();
        echo 'gata';
    }
    private function recursive_match($expr, $n){
        $n++;
        if ($n>20){
            echo 'bucla infinita'; die();
        }
        $return_val = "";
        //str_replace("(".$matches[1][0].")", "T", $expr );
        $pattern = '#\(((?>[^()]+)|(?R))*\)#x';
        preg_match($pattern, $expr, $matches, PREG_OFFSET_CAPTURE);

        if (count($matches)>0){
            /*in substringul nostru mai avem paranteze*/
            $return_val = $this->recursive_match($expr, $n);

        }
        else{
            /*nu mai avem paranteze*/
            $return_val = $this->values_without_brackets($expr);
        }
        return $return_val;

    }

    private function values_without_brackets($expr){

        $return_val = "";
        $length = strlen($expr);
        $stored_operator = "";
        for ($i = 0; $i < $length; $i++) {
            if ( !in_array($expr[$i], array('|', '&') ) ){
                if ($return_val ==""){
                    $return_val = $expr[$i];
                }
                else{
                    /*trebuie sa avem prima valoare, operatorul , si urmatoarea valoare - toate necesare pentru calcul*/
                    $result = $this->calculate_one_statement($return_val , $stored_operator, $expr[$i]);
                    if ($result){
                        str_replace("(".$matches[1].")", "T", $expr );
                    }
                    else{
                        str_replace("(".$matches[1].")", "F", $expr );
                    }
                    $stored_operator = "";
                }
            }
            else{
                $stored_operator  = $expr[$i];
            }
        }
        return $expr;
    }


    private function calculate_one_statement($first_value, $operator, $second_value){

        switch ($operator) {
            case "|":
                return $this->bool_values[$first_value] || $this->bool_values[$second_value];
                break;
            case "&":
                return $this->bool_values[$first_value] && $this->bool_values[$second_value];
                break;
            default:
                echo 'nu avem oerandul care trebuie; returnam true';
                return true;

        }

    }

}