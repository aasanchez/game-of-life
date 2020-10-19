<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LifeCommand extends Command {

    protected static $defaultName = 'life:new-game';
    public $cells = [];
    public $opt = [];

    public function __construct() {
        $this->opt = [
            "height" => exec('tput lines') - 7,
            "width" => exec('tput cols'),
            "FPS"=>60,
            "odds"=>5
        ];
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setDescription('Start a new Game')
            ->setHelp('
The universe of the Game of Life is an infinite, two-dimensional orthogonal grid
of square cells, each of which is in one of two possible states, live or dead, 
(or populated and unpopulated, respectively). Every cell interacts with its 
eight neighbours, which are the cells that are horizontally, vertically, or
diagonally adjacent. At each step in time, the following transitions occur:
            
* Any live cell with fewer than two live neighbours dies, as if by 
  underpopulation.
* Any live cell with two or three live neighbours lives on to the next
  generation.
* Any live cell with more than three live neighbours dies, as if by
  overpopulation.
* Any dead cell with exactly three live neighbours becomes a live cell, as if 
  by reproduction.
            
These rules, which compare the behavior of the automaton to real life, can be 
condensed into the following:
            
* Any live cell with two or three live neighbours survives.
* Any dead cell with three live neighbours becomes a live cell.
* All other live cells die in the next generation. Similarly, all other dead 
  cells stay dead.
');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $frame = 0;
        $time = 0;
        $io = new SymfonyStyle($input, $output);
        $game = $this->generateCells();
        while (true) {
            $frame++;
            $output->write($this->ClearScreen());
            foreach ($this->cells as $key => $row) {
                $print_row = null;
                foreach ($row as $key2 => $val) {
                    $char = " ";
                    if ($this->cells[$key][$key2] == 1) {
                        $char = "#";
                    }
                    $print_row .= $char;
                }
                $output->writeln($print_row);
            }


            if ($frame == $this->opt["FPS"]) {
                $time++;
                $frame = 0;
            }
            $io->table(
                ['X', 'Y', 'Seconds'],
                [
                    [$this->opt["width"], $this->opt["height"], $time." sec"],
                ]
            );
            usleep(1000000 / $this->opt["FPS"]);
        }
//        return Command::SUCCESS;
    }

    protected function ClearScreen() {
        return sprintf("\033\143");
    }

    protected function generateCells() {

        for ($x = 0; $x < $this->opt["width"]; $x++) {
            for ($y = 0; $y < $this->opt["height"]; $y++) {
                $cell = 0;
                $seed = mt_rand(0, $this->opt["odds"]);
                if ($seed ===  $this->opt["odds"]) {
                    $cell = 1;
                }
                $this->cells[$y][$x] = $cell;
            }
        }
        return $this;
    }

    protected function render(){

    }
}