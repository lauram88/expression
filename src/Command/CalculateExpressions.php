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

    /*prepara expresia pentru a putea fi calculata prin rpn : Ex: ne transforma o expresie: T&F in TF&*/
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