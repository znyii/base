<?php

namespace ZnYii\Base\Helpers;

use yii\web\UploadedFile;

class UploadHelper
{

    public static function createUploadedFileArray(array $inputFiles): array
    {
        $files = [];
        foreach ($inputFiles['RequestForm']['name']['files'] as $index => $name) {
            if($inputFiles['RequestForm']['error']['files'][$index] == 0) {
                $file = [
                    'name' => $inputFiles['RequestForm']['name']['files'][$index],
                    'type' => $inputFiles['RequestForm']['type']['files'][$index],
                    'tempName' => $inputFiles['RequestForm']['tmp_name']['files'][$index],
                    'error' => $inputFiles['RequestForm']['error']['files'][$index],
                    'size' => $inputFiles['RequestForm']['size']['files'][$index],
                ];
                $files[] = new UploadedFile($file);
            }
        }
        return $files;
    }

}
