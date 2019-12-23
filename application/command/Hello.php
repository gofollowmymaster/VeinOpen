<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/13
 * Time: 17:48
 * description:描述
 */

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Hello extends Command
{
    protected function configure()
    {
        $this->setName('hello')
             ->addArgument('name', Argument::OPTIONAL, "your name")
             ->addOption('city', null, Option::VALUE_REQUIRED, 'city name')
             ->setDescription('Say Hello');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));
        $name = $name ?: 'thinkphp';

        if ($input->hasOption('city')) {
            $city = PHP_EOL . 'From ' . $input->getOption('city');
        } else {
            $city = '';
        }

        $output->writeln("Hello," . $name . '!' . $city);
    }
}
