<?php

declare(strict_types=1);

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class CustomFormFactory
{
    use Nette\SmartObject;

    public function create(): Form
    {
        return new Form;
    }
}