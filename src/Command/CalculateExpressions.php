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
    private $boolValues = array("T"=>true, "F"=>false);

    protected function configure()
    {
        $this
            ->setName('evaluate')
            ->setDescription('Calculate Expression')
            ->addArgument(
                'expression',
                InputArgument::OPTIONAL,
                ''
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $expression = $input->getArgument('expression');

        $expression = $this->fixForRpn($expression);

        $result = $this->reversePolishNotation($expression);

        if ($result=="") {
            $result = "Please verify if the expression you asked for is correct ( Eg: (F&(T&F))|T&(F|T)&F|T  ; T|F&T&(F|T&(T|F)) )";
        }

        $text = 'Final Answer: '.$result ;

        $output->writeln($text);
    }


    function fixForRpn($expression){
        $newExpression = array();
        $string = "";
        $operandsList = array();
        $values = array('T'=>true, 'F'=>false);
        $length = strlen($expression);
        $i = 0;
        $push = true;
        while ($i<$length) {
            if ($expression[$i]=="(") {
                $push = false;
            }elseif ($expression[$i]==")") {
                $push = true;
                $val = array_pop($operandsList);
                array_push($newExpression, $val);
                $string.=$val;
                if (count($operandsList)>0) {
                    $val = array_pop($operandsList);
                    array_push($newExpression, $val);
                    $string.=$val;
                }
            }elseif (in_array($expression[$i], array("&", "|"))) {
                array_push($operandsList, $expression[$i]);
            }elseif (in_array($expression[$i], array_keys($values))) {
                array_push($newExpression, $expression[$i]);
                $string.=$expression[$i];
                if ($push) {
                    $val = array_pop($operandsList);
                    if ($val!="") {
                        array_push($newExpression, $val);
                        $string.=$val;
                    }
                }
            }
            $i++;
        }
        if (count($operandsList)>0) {
            $val = array_pop($operandsList);
            array_push($newExpression, $val);
            $string.=$val;
        }
        return $newExpression;
    }


    function reversePolishNotation($params) {
        $values = array('T'=>true, 'F'=>false);
        $count = sizeof($params);
        $result = null;
        $valueToAdd = null;
        $numeric = array();

        for($i = 0; $i < $count; $i++) {
            if ( in_array( $params[$i], array_keys($values) ) ) {
                $numeric[] = $params[$i];
            } else {
                if (count($numeric)>1){
                    $pop1 = array_pop($numeric);
                    $pop2 = array_pop($numeric);
                    switch ($params[$i]) {
                        case "&":
                            $result = $values[$pop1] && $values[$pop2];
                            break;
                        case "|":
                            $result = $values[$pop1] || $values[$pop2];
                            break;
                    }
                }
                if ($result) {
                    $valueToAdd = "T";
                }else{
                    $valueToAdd = "F";
                }
                array_push($numeric, $valueToAdd);
            }
        }

        if ($count==1) {
            $valueToAdd = array_pop($numeric);
        }
        return $valueToAdd;
    }

    private function CalcExpr($expr){

        $pattern = '/\(+(.*?)\)/';

        preg_match($pattern, $expr, $matches, PREG_OFFSET_CAPTURE);

        /*cat timp mai avem paraneze rulam recursive match*/
        while (is_string($expr) && (strlen($expr)>1)) {

            $expr = $this->RecursiveMatch($expr);

        }

        return $expr;
    }

    private function RecursiveMatch($expr){

        /*cat timp noi avem paranteza in paranteza vom merge recursiv ruland tot aceasta functie pe subparanteza gasita*/
        $pattern = '/\(+(.*?)\)/';

        preg_match($pattern, $expr, $matches, PREG_OFFSET_CAPTURE);

        //print_r($matches);

        if (count($matches)>0) {
            /*in substringul nostru mai avem paranteze*/
            $return_val = $this->RecursiveMatch($matches[1][0]);

            /*inlocuim resultatul cu informatia din $expr*/
            if (!is_bool($return_val)) {

                if ($return_val=="T") {

                    $return_val = true;

                } else {

                    $return_val = false;

                }
            }
            if ($return_val ) {

                $bool_vall= str_replace("(".$matches[1][0].")", "T", $expr );

            } else {

                $bool_vall = str_replace("(".$matches[1][0].")", "F", $expr );

            }
            print_r("valoare de dupa str_replace:");
            var_dump($bool_vall);
        } else {
            /*nu mai avem paranteze*/
            $bool_vall_result = $this->ValuesWithoutBrackets($expr);

            if (is_bool($bool_vall_result)) {

                if ($bool_vall_result) {

                    $bool_vall="T";

                } else {

                    $bool_vall="F";

                }
            } else {

                $bool_vall = $bool_vall_result;

            }
        }

        return $bool_vall;

    }

    private function ValuesWithoutBrackets($expr)
    {
        /*putem avea o epresie de genul : T&F|F|F - vom mlerge pe fiecare caracter din aceasta expresie si vom calcula pe rand */
        $return_val = "";

        $length = strlen($expr);

        $stored_operator = "";

        for ($i = 0; $i < $length; $i++) {

            if ( !in_array($expr[$i], array('|', '&') ) ) {

                if ($return_val =="") {

                    $return_val = $expr[$i];

                } else {

                    /*trebuie sa avem prima valoare, operatorul , si urmatoarea valoare - toate necesare pentru calcul*-*/
                    $return_val = $this->CalculateOneStatement($return_val , $stored_operator, $expr[$i]);

                    if ($return_val) {

                        $return_val = "T";

                    } else {

                        $return_val = "F";

                    }

                    $stored_operator = "";

                }
            } else {

                $stored_operator  = $expr[$i];

            }
        }
        return $return_val;
    }

    private function CalculateOneStatement($first_value, $operator, $second_value)
    {

        /* aceasta calculeaza doar expresiile cu doua valori*/
        switch ($operator) {

            case "|":

                return ($this->boolValues[$first_value] || $this->boolValues[$second_value]);

                break;

            case "&":

                return ($this->boolValues[$first_value] && $this->boolValues[$second_value]);

                break;

            default:

                echo 'nu avem operandul care trebuie; returnam true';

                return true;
        }
    }

}
