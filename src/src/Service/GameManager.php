<?php

namespace App\Service;

class GameManager
{
    private $opts = [];

    private $startTime = 0;

    public function __construct()
    {
        $this->setDefaults();
        $this->startTime = time();
    }

    private function setDefaults()
    {
        $defaults = [
            'timeout' => 5000,
            'rand_max' => 5,
            'realtime' => true,
            'max_frame_count' => 0,
            'template' => null,
            'keep_alive' => 0,
            'random' => true,
            'width' => exec('tput cols'),
            'height' => exec('tput lines'),
            'cell' => 'O',
            'empty' => ' ',
        ];

        $this->opts += $defaults;
    }

    private function render()
    {

    }

    public function loop()
    {

    }
}