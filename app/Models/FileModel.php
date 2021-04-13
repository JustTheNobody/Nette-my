<?php

declare(strict_types=1);

namespace App\Models;

use SplFileInfo;

use Nette\Utils\Image;
use Nette\Utils\Finder;
use Nette\Neon\Exception;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

class FileModel
{


    const
    DEFAULT_AVATAR_DIR = IMG_DIR . '/avatar',
    FILE_DIR = WWW_DIR . '/storage';

    private $imageStorage;
    
    public UserModel $user;

    public function __construct(UserModel $user)
    {
        $this->user = $user;
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

    //return new file name
    public function upload(FileUpload $file, $category)
    {
        if (!$file->isOk() || !$file->isImage()) {
            return false;
        }

        $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
        $newName = time() . "." . $ext;

        if ($category != "avatar") {
            $fileSavePath = self::FILE_DIR .  "/" . lcfirst($category) . "/" . $newName;
        } else {

            $targetDir = self::FILE_DIR . "/_users/" . $this->user->testUser->getIdentity()->getID() . "/avatar/";
            //delete old avatar dir first
            FileSystem::delete($targetDir);

            $fileSavePath = $targetDir . $newName;
        }
        
        return ($file->move($fileSavePath)->isOk())? $newName : false;
    }

    public function copyDefaultAvatar($avatar)
    {                   
            $fileOrigin = self::DEFAULT_AVATAR_DIR . "/" . $avatar;
            
            $targetDir = self::FILE_DIR . "/_users/" . $this->user->testUser->getIdentity()->getID() . "/avatar/";
            //delete od avatar dir first
            FileSystem::delete($targetDir);

            $fileTarget = $targetDir . $avatar;
            FileSystem::copy($fileOrigin, $fileTarget);
    }

    public function deleteFile($fileName, $category)
    {
        $targetFile = self::FILE_DIR . "/".lcfirst($category)."/" . $fileName;
        FileSystem::delete($targetFile);
    }
}
