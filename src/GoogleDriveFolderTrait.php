<?php

namespace samuelelonghin\google\drive;

use Google\Exception;
use Google\Service\Drive;

/**
 * @property-read  GoogleDriveBase $googleDriveBase
 * @property-read ?string $googleDriveFolderIdAttributeName
 * @property-read   ?string $googleDriveFolderName
 */
trait GoogleDriveFolderTrait
{
    private ?GoogleDriveBase $_drive = null;

    abstract protected function getGoogleDriveFolderIdAttributeName(): ?string;

    abstract protected function getGoogleDriveFolderName(): ?string;

    abstract protected function getGoogleDriveFolderParents(): array;

    abstract public function save($runValidation = true, $attributes = null);

    /**
     * @throws Exception
     */
    public function getGoogleDriveFolder(): string
    {
        $attribute = $this->getGoogleDriveFolderIdAttributeName();
        if (($googleDriveFolderId = $this->{$attribute})) {
            return $googleDriveFolderId;
        }
        try {

            $fileMetadata = new Drive\DriveFile([
                'name' => $this->getGoogleDriveFolderName(),
                'mimeType' => 'application/vnd.google-apps.folder']);

            $fileMetadata->setParents($this->getGoogleDriveFolderParents());

            $file = $this->googleDriveBase->service->files->create($fileMetadata, [
                'fields' => 'id'
            ]);
            $this->{$attribute} = $file->id;
            $this->save(true, [$attribute]);
        } catch (Exception $e) {
            throw $e;
        }

        return $this->{$attribute};
    }

    protected function getGoogleDriveBase(): ?GoogleDriveBase
    {
        if (!$this->_drive) {
            $this->_drive = new GoogleDriveBase();
        }
        return $this->_drive;
    }

}