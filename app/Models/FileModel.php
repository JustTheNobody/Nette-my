<?php

declare(strict_types=1);

namespace App\Models;

use SplFileInfo;

use Nette\Utils\Html;
use Nette\Utils\Finder;
use Nette\Http\FileUpload;

class FileModel
{
    const
    AVATAR_DIR = __DIR__ . '/../../www/img/avatar';

    public function __construct()
    {
        
    }
    //check if the user folder exist, if not create

    //upload avatar after checking if (jpg,png, gif)

    //get default avatars file names from folder
    public function getAvatars()
    {
        foreach (Finder::findFiles(['*.png', '*.jpg', '*.gif'])->from(self::AVATAR_DIR) as $key => $file) {
            $fileInfo = new SplFileInfo($key);
            $avatar = $fileInfo->getFilename();
            $avatars[] = $avatar;
        }
        return $avatars;
    }
}
