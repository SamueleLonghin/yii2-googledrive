<?php

namespace samuelelonghin\google\drive;

use Google\Client;
use Google\Service;
use Google\Service\Drive\DriveFile;

/**
 *
 * @property-read Client|null $client
 * @property-read Service\Drive $service
 */
class GoogleDriveUploadedFile extends GoogleDriveBase
{
    public DriveFile $result;

    public function saveOnGoogleDriveAs($name, $parents, $deleteTempFile = true): bool
    {
        if (!$this->service)
        {
            return false;
        }
        /**
         * Get file
         */
        $this->_file->setName($name);
        $this->_file->setParents($parents);
        $this->result = $this->service->files->create(
            $this->_file,
            [
                'data' => file_get_contents($this->tempName),
                'mimeType' => 'application/octet-stream',
                'uploadType' => 'resumable',
            ]
        );

        return !$deleteTempFile || unlink($this->tempName);
    }

    public static function getInstance($model, $attribute): ?self
    {
        return parent::getInstance($model, $attribute);
    }
}
