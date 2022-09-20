<?php

namespace samuelelonghin\google\drive;

use Google\Client;
use Google\Exception;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use yii\web\UploadedFile;
use samuelelonghin\google\auth\GoogleAuthTrait;

/**
 *
 * @property-read Client|null $client
 */
class GoogleDriveUploadedFile extends UploadedFile
{
    use GoogleAuthTrait;

    /**
     * @var DriveFile $_file
     */
    public DriveFile $_file;
    protected Client $_client;
    public DriveFile $result;


    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_file = new DriveFile();
    }

    public function saveOnGoogleDriveAs($name, $parents, $deleteTempFile = true): bool
    {
        if ($this->hasError && false) {
            return false;
        }
        if (!($service = new Drive($this->client))) {
            return false;
        }

        /**
         * Get file
         */
        $this->_file->setName($name);
        $this->_file->setParents($parents);
        $this->result = $service->files->create(
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
