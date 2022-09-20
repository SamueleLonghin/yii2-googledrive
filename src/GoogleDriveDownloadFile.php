<?php

namespace samuelelonghin\google\drive;

use Google\Client;
use Google\Exception;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Yii;
use yii\web\UploadedFile;
use samuelelonghin\google\GoogleAuthTrait;

/**
 *
 * @property-read Client $client
 */
class GoogleDriveDownloadFile extends UploadedFile
{
    use GoogleDriveAuthTrait;
    /**
     * @var DriveFile $_file
     */
    public DriveFile $_file;
    protected Client $_client;
    public $result;
    public $filePath = null;


    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_file = new DriveFile();
    }

    public static function getInstanceById($id, $options = []): ?GoogleDriveDownloadFile
    {
        $model = (new self());
        if (!($service = new Drive($model->client))) {
            return null;
        }

        /**
         * Get file Instance
         */

        // Get the authorized Guzzle HTTP client
        $http = $model->client->authorize();

        // Download in 1 MB chunks
        $chunkSizeBytes = 1 * 1024 * 1024;
        $chunkStart = 0;


        $file = $service->files->get($id, [
            'fields' => 'size,fileExtension,name'
        ]);
        if ($file) {
            $model->size = $file->size;
            $model->name = $file->name;

            $model->filePath = sprintf(Yii::getAlias('@app/runtime/drive/%s'), $id) . '.' . $model->extension;

            // Open a file for writing
            $fp = fopen($model->filePath, 'w');

            // Iterate over each chunk and write it to our file
            while ($chunkStart < $model->size) {
                $chunkEnd = $chunkStart + $chunkSizeBytes;
                $response = $http->request(
                    'GET',
                    sprintf('/drive/v3/files/%s', $id),
                    [
                        'query' => ['alt' => 'media'],
                        'headers' => [
                            'Range' => sprintf('bytes=%s-%s', $chunkStart, $chunkEnd)
                        ]
                    ]
                );
                $chunkStart = $chunkEnd + 1;
                fwrite($fp, $response->getBody()->getContents());
            }
            // close the file pointer
            fclose($fp);
        }
        return $model;
    }

    public function removeLocal(): bool
    {
        return unlink($this->filePath);
    }

    /**
     * @throws Exception
     */
    public function getClient(): ?Client
    {
        $this->_client = new Client();

        if ($credentials_file = $this->checkServiceAccountCredentialsFile()) {
            // set the location manually
            $this->_client->setAuthConfig($credentials_file);
        } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            // use the application default credentials
            $this->_client->useApplicationDefaultCredentials();
        } else {
            echo $this->missingServiceAccountDetailsWarning();
            return null;
        }

        $this->_client->addScope("https://www.googleapis.com/auth/drive");
        return $this->_client;
    }
}
