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

        if ($expression=="") {
            $result = "Please verify if the expression you asked for is correct ( Eg: (F&(T&F))|T&(F|T)&F|T  ; T|F&T&(F|T&(T|F)) )";
        }else{
            $result = $this->reversePolishNotation($expression);

            if ($result=="") {
                $result = "Please verify if the expression you asked for is correct ( Eg: (F&(T&F))|T&(F|T)&F|T  ; T|F&T&(F|T&(T|F)) )";
            }
        }


        

        $text = 'Final Answer: '.$result ;

        $output->writeln($text);
    }

    /*prepara expresia pentru a putea fi calculata prin rpn : Ex: ne transforma o expresie: T&F in TF&*/
    function fixForRpn($expression){

        $newExpression = array();

        $string = "";

        $bracketsLevel = 0;

        $operandsOnLevel = array();

        $valuesOnLevel = array();

        $operandsList = array();

        $lasValueEval = 0;/*0- boolean value, 1- operand*/

        $values = array('T'=>true, 'F'=>false);

        $length = strlen($expression);

        $i = 0;

        $push = true;

        $wrongExpr = false;

        while ($i<$length) {

            if ($expression[$i]=="(") {

                $push = false;

                $bracketsLevel++;

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

                $bracketsLevel--;


            }elseif (in_array($expression[$i], array("&", "|"))) {
                if ($lasValueEval==2){
                    $wrongExpr = true;
                }
                /*verificam daca mai avem operanzi pe acest nivel si daca avem il scoatem pe ultimul inainte de a trece la urmatorul nivel de paranteze;*/
                if ( (isset($operandsOnLevel[$bracketsLevel]) and ($operandsOnLevel[$bracketsLevel]>0) ) and (isset($valuesOnLevel[$bracketsLevel]) and ($valuesOnLevel[$bracketsLevel]>1) )) {
                    $val = array_pop($operandsList);

                    if ($val!="") {

                        array_push($newExpression, $val);

                        $string.=$val;

                        $operandsOnLevel[$bracketsLevel]--;

                    }
                }

                array_push($operandsList, $expression[$i]);

                if (!isset($operandsOnLevel[$bracketsLevel])) {

                    $operandsOnLevel[$bracketsLevel] = 1;

                }else{

                    $operandsOnLevel[$bracketsLevel]++;

                }

                $lasValueEval = 2;

            }elseif (in_array($expression[$i], array_keys($values))) {

                if ($lasValueEval==1){
                    $wrongExpr = true;
                }

                array_push($newExpression, $expression[$i]);

                $string.=$expression[$i];

                if (!isset($valuesOnLevel[$bracketsLevel])) {

                    $valuesOnLevel[$bracketsLevel] = 1;

                }else{

                    $valuesOnLevel[$bracketsLevel]++;

                }

                if ($push) {

                    $val = array_pop($operandsList);

                    if ($val!="") {

                        array_push($newExpression, $val);

                        $string.=$val;

                    }

                }
                $lasValueEval = 1;

            }else{
                return "";
            }

            $i++;

        }

        if (count($operandsList)>0) {

            $val = array_pop($operandsList);

            array_push($newExpression, $val);

            $string.=$val;

        }

        if ( ($bracketsLevel!=0) or ($wrongExpr) ) {
            /*a uitat cel putin o paranteza*/
            $newExpression = "";
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

                if (count($numeric)>1) {

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

}