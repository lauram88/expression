<?php
/**
 * Created by PhpStorm.
 * User: laura.magureanu
 * Date: 14.04.2016
 * Time: 17:31
 */

namespace Laura\Expressions\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateExpressions extends Command
{
    var $bool_values = array("T"=>true, "F"=>false);

    protected function configure()
    {
        $this
            ->setName('evaluate')
            ->setDescription('Calculate Expression')
            ->addArgument(
                'expression',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $expression = $input->getArgument('expression');

        $result = $this->CalcExpr($expression);

        //$result = $this->rpn($expression);

        //if ($result ) {
          //  $text = "true";
        //} else {
          //  $text = "false";

        //}

        $text = $result ;
        $output->writeln($text);
    }

    private function CalcExpr($expr){
        //$pattern = '/^()/';
        //$pattern = '/\((.*?)\)/si';
        //$pattern = '/\(+(.*?)\)/';
        //$pattern  = '/\(+(.*?)\)/';
        //$pattern  = '/(?<=\()(.+)(?=\))/is';
        $pattern = '/\(+(.*?)\)/';
        preg_match($pattern, $expr, $matches, PREG_OFFSET_CAPTURE);
        $i = 0;
        /*cat timp mai avem paraneze rulam recursive match*/
        while (is_string($expr) && (strlen($expr)>1)){
            $i++;
            $expr = $this->RecursiveMatch($expr, 0);
        }
        return $expr;
    }
    private function RecursiveMatch($expr, $n){
        /*cat timp noi avem paranteza in paranteza vom merge recursiv ruland tot aceasta functie pe subparanteza gasita*/
        $n++;
        if ($n>11){
            echo 'bucla infinita2 buclucasa'; die();
        }
        $return_val = "";
        $bool_vall = "";
        $pattern = '/\(+(.*?)\)/';
        preg_match($pattern, $expr, $matches, PREG_OFFSET_CAPTURE);
        print_r($matches);
        if (count($matches)>0){
            /*in substringul nostru mai avem paranteze*/
            $return_val = $this->RecursiveMatch($matches[1][0], $n);
            /*inlocuim resultatul cu informatia din $expr*/
            if (!is_bool($return_val)){
                if ($return_val=="T"){
                    $return_val = true;
                }
                else{
                    $return_val = false; 
                }
            }
            if ($return_val ){
                $bool_vall= str_replace("(".$matches[1][0].")", "T", $expr );
            }
            else{
                $bool_vall = str_replace("(".$matches[1][0].")", "F", $expr );
            }
            print_r("valoare de dupa str_replace:");
            var_dump($bool_vall);
        }
        else{
            /*nu mai avem paranteze*/
            $bool_vall_result = $this->ValuesWithoutBrackets($expr);
            if (is_bool($bool_vall_result)){
                if ($bool_vall_result){
                    $bool_vall="T";
                }
                else{
                    $bool_vall="F";
                }
            }
            else{
                $bool_vall = $bool_vall_result;

            }
        }
        return $bool_vall;

    }

    private function ValuesWithoutBrackets($expr){
        /*putem avea o epresie de genul : T&F|F|F - vom mlerge pe fiecare caracter din aceasta expresie si vom calcula pe rand */
        $return_val = "";
        $length = strlen($expr);
        $stored_operator = "";
        for ($i = 0; $i < $length; $i++) {
            if ( !in_array($expr[$i], array('|', '&') ) ){
                if ($return_val ==""){
                    $return_val = $expr[$i];
                }
                else{
                    /*trebuie sa avem prima valoare, operatorul , si urmatoarea valoare - toate necesare pentru calcul*-*/
                    $return_val = $this->CalculateOneStatement($return_val , $stored_operator, $expr[$i]);
                    if ($return_val){
                        $return_val = "T";
                    }
                    else{
                        $return_val = "F";
                    }
                    $stored_operator = "";
                }
            }
            else{
                $stored_operator  = $expr[$i];
            }
        }
        return $return_val;
    }


    private function CalculateOneStatement($first_value, $operator, $second_value){
        /* aceasta calculeaza doar expresiile cu doua valori*/
        switch ($operator) {
            case "|":
                return ($this->bool_values[$first_value] || $this->bool_values[$second_value]);
                break;
            case "&":
                return ($this->bool_values[$first_value] && $this->bool_values[$second_value]);
                break;
            default:
                echo 'nu avem operandul care trebuie; returnam true';
                return true;

        }

    }



    function rpn($params) {



        $pattern = '#\(((?>[^()]+)|(?R))*\)#x';
        preg_match_all($pattern, $params, $matches, PREG_OFFSET_CAPTURE);

        /*in matches avem pozitiile de la care incep parantezele*/
        print_r($matches);

        /*incercam sa creem un array care sa contina */
        $params = explode(' ', $params);

        $count = sizeof($params);

        $result = null;

        $numeric = array();


        for($i = 0; $i < $count; $i++) {

            if (is_numeric($params[$i])) {

                $numeric[] = $params[$i];

            } else {

                switch ($params[$i]) {

                    case "+":
                        $result = array_pop($numeric) + array_pop($numeric);
                        break;
                    case "-":
                        $result = array_pop($numeric) - array_pop($numeric);
                        break;
                    case "*":
                        $result = array_pop($numeric) * array_pop($numeric);
                        break;
                    case "/":
                        $result = array_pop($numeric) / array_pop($numeric);
                        break;
                }
                array_push($numeric, $result);
                //echo "<pre>";
                //print_r($result);
                //echo "</pre>";

            }
        }

        return $result;
    }

    function rpn_modified($params) {

        $params = explode(' ', $params);

        $count = sizeof($params);

        $result = null;

        $numeric = array();

        for($i = 0; $i < $count; $i++) {

            if (in_array($params[$i], $this->bool_values )) {

                $numeric[] = $params[$i];

            } else {

                switch ($params[$i]) {

                    case "|":
                        $result = $this->bool_values[array_pop($numeric)] || $this->bool_values[array_pop($numeric)];
                        break;
                    case "&":
                        $result = $this->bool_values[array_pop($numeric)] && $this->bool_values[array_pop($numeric)];
                        break;
                    case "(":
                        //$result = array_pop($numeric) * array_pop($numeric);
                        break;
                    case ")":
                        //$result = array_pop($numeric) / array_pop($numeric);function rpn_modified($params) {

                        $params = explode(' ', $params);

                        $count = sizeof($params);

                        $result = null;

                        $numeric = array();

                        for($i = 0; $i < $count; $i++) {

                            if (in_array($params[$i], $this->bool_values )) {

                                $numeric[] = $params[$i];

                            } else {

                                switch ($params[$i]) {

                                    case "|":
                                        $result = $this->bool_values[array_pop($numeric)] || $this->bool_values[array_pop($numeric)];
                                        break;
                                    case "&":
                                        $result = $this->bool_values[array_pop($numeric)] && $this->bool_values[array_pop($numeric)];
                                        break;
                                    case "(":
                                        //$result = array_pop($numeric) * array_pop($numeric);
                                        break;
                                    case ")":
                                        //$result = array_pop($numeric) / array_pop($numeric);
                                        break;
                                }
                                array_push($numeric, $result);
                            }
                        }
                        return $result;
                }
                break;
            }
            array_push($numeric, $result);
            echo "<pre>";
            print_r($result);
            echo "</pre>";
        }

        return $result;
    }


}
