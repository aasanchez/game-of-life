<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LifeCommand extends Command
{

    protected static $defaultName = 'life:new-game';

    /** @var array */
    public $cells = [];

    /** @var array */
    public $opt = [];

    public function __construct()
    {
        $this->opt = [
            "height" => intval(exec('tput lines')) ? intval(exec('tput lines')) - 7 : 35 ,
            "width" => intval(exec('tput cols'))? intval(exec('tput cols')) : 120 ,
            "FPS" => 20,
            "odds" => 1,
        ];
        parent::__construct();
    }

    protected function configure(): void
    {
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

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $status = new SymfonyStyle($input, $output);
        $startTime = microtime(true);
        $this->generateCells();

        while (true) {
            $output->write($this->ClearScreen());
            foreach ($this->cells as $y => $row) {
                $toPrint = '';
                foreach (array_keys($row) as $x) {
                    $state = " ";
                    if ($this->cells[$y][$x] == 1) {
                        $state = "█";
                    }
                    $toPrint .= $state;
                }
                $output->writeln('<fg=black;bg=white>'.$toPrint.'</>');
            }

            $endTime = microtime(true);

            $status->table(
                ['Width', 'Height', 'Time', 'Living cells', 'FPS'],
                [
                    [
                        $this->opt["width"],
                        $this->opt["height"],
                        $this->getTime($endTime - $startTime),
                        $this->countCells().'/'.$this->opt["width"] * $this->opt["height"],
                        $this->opt["FPS"]
                    ],
                ]
            );

            //New Generation
            $this->newGeneration();

            usleep(1000000 / $this->opt["FPS"]);
        }
        return Command::SUCCESS;
    }

    protected function newGeneration(): void
    {
        $grid = $this->cells;

        [$toKill, $toBorn] = $this->neighborsWeeper($grid);

        foreach ($toKill as $c) {
            $grid[$c[0]][$c[1]] = 0;
        }

        foreach ($toBorn as $c) {
            $grid[$c[0]][$c[1]] = 1;
        }

        $this->cells = $grid;
    }

    protected function neighborsWeeper(array $grid) : array
    {
        $toKill = [];
        $toBorn = [];
        for ($y = 0; $y < $this->opt["height"]; $y++) {
            for ($x = 0; $x < $this->opt["width"]; $x++) {
                $neighbors = $this->getNeighbors($x, $y);

                if ($grid[$y][$x] && ($neighbors < 2 || $neighbors > 3)) {
                    $toKill[] = [$y, $x];
                }
                if (!$grid[$y][$x] && $neighbors === 3) {
                    $toBorn[] = [$y, $x];
                }
            }
        }

        return [$toKill, $toBorn];
    }

    /**
     *
     * @SuppressWarnings("CyclomaticComplexity")
     */
    public function getNeighbors(int $xAxis, int $yAxis) : int
    {
        $neighbors = 0;
        for ($y2 = $yAxis - 1; $y2 <= $yAxis + 1; $y2++) {
            if ($y2 < 0 || $y2 >= $this->opt["height"]) {
                continue;
            }
            for ($x2 = $xAxis - 1; $x2 <= $xAxis + 1; $x2++) {
                if (($x2 == $xAxis && $y2 == $yAxis) || ($x2 < 0 || $x2 >= $this->opt["width"])) {
                    continue;
                }
                if ($this->cells[$y2][$x2]) {
                    $neighbors += 1;
                }
            }
        }
        return $neighbors;
    }

    protected function clearScreen(): string
    {
        return sprintf("\033\143");
    }

    protected function generateCells(): object
    {

        for ($x = 0; $x < $this->opt["width"]; $x++) {
            for ($y = 0; $y < $this->opt["height"]; $y++) {
                $cell = 0;
                $seed = mt_rand(0, $this->opt["odds"]);
                if ($seed === $this->opt["odds"]) {
                    $cell = 1;
                }
                $this->cells[$y][$x] = $cell;
            }
        }
        return $this;
    }

    protected function countCells(): int
    {
        $count = 0;
        foreach ($this->cells as $y => $row) {
            foreach (array_keys($row) as $x) {
                if ($this->cells[$y][$x] === 1) {
                    $count++;
                }
            }
        }

        return $count;
    }

    protected function getTime(float $seconds): string
    {
        $hour = floor($seconds / 3600);
        $min = floor($seconds / 60 % 60);
        $sec = floor($seconds % 60);

        return sprintf('%02d:%02d:%02d', $hour, $min, $sec);
    }
}
