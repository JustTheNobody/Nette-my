<?php

declare(strict_types=1);

namespace App\Models;

use SplFileInfo;

use Nette\Utils\Image;
use Nette\Utils\Finder;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

class FileModel
{
    const
    DEFAULT_AVATAR_DIR = IMG_DIR . '/avatar',
    AVATAR_DIR = WWW_DIR . '/storage';

    private $imageStorage;
    
    public function __construct() 
    {
        
    }

    //get default avatars file names from folder
    public function getAvatars()
    {
        foreach (Finder::findFiles(['*.png', '*.jpg', '*.gif'])->from(self::DEFAULT_AVATAR_DIR) as $key => $file) {
            $fileInfo = new SplFileInfo($key);
            $avatars[] = $fileInfo->getFilename();
        }
        return $avatars;
    }

    //Todo!!
    public function upload(FileUpload $file)
    {
        if (!$file->isOk() || !$file->isImage()) {
            return false;
        }

        /**
         * TODO
         */

    }

//---------------------------------------------
    //upload avatar after checking if (jpg, png, gif)
    public function uploadAvatar($file)
    {

        echo "sorry not ready yet";
        exit;
        //create user folder
        FileSystem::createDir(self::AVATAR_DIR."/".$this->user->testUser->getIdentity()->getID(), 0777);

        //echo $imageStorage;

        //TODO


        $request = new FileUpload($file);
        $file = $request->getFile('avatar')->hasFile();
        //get file type
        $ext = $file['avatar']['type'];
        if ($file instanceof FileUpload) {
            $request->move("storage/" . $this->user->testUser->getIdentity()->getID());
            return true;
        }
        return false;
    }
}
