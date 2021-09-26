<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class CustomFormFactory
{
    use Nette\SmartObject;

    public array $colors = ['Blue', 'Red', 'Green', 'Yellow'];

    public function create(): Form
    {
        return new Form;
    }

    public function colors()
    {
        $randColorKey = rand(0, count($this->colors)-1);

        return [$this->colors[$randColorKey], $randColorKey];
    }
}