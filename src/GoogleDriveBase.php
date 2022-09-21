<?php

namespace samuelelonghin\google\drive;

use Google\Client;
use Google\Service;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use samuelelonghin\google\auth\GoogleAuthTrait;
use yii\web\UploadedFile;

/**
 *
 * @property-read ?Drive $service
 * @property-read ?Client $client
 */
class GoogleDriveBase extends UploadedFile
{
    use GoogleAuthTrait;

    public DriveFile $_file;
    private ?Drive $_service = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_file = new DriveFile();
    }

    public function getService(): ?Drive
    {
        if (!$this->_service) {
            $this->client->addScope(Drive::DRIVE);
            $this->_service = new Drive($this->client);
        }
        return $this->_service;
    }
}