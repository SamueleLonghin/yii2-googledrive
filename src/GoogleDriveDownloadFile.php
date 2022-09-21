<?php

namespace samuelelonghin\google\drive;

use Google\Client;
use Google\Service;
use GuzzleHttp\Exception\GuzzleException;
use Yii;

/**
 *
 * @property-read Client $client
 * @property-read Service\Drive $service
 */
class GoogleDriveDownloadFile extends GoogleDriveBase
{
    public ?string $filePath = null;

    /**
     * @throws GuzzleException
     */
    public static function getInstanceById($id, $options = []): ?GoogleDriveDownloadFile
    {
        $model = (new self());
        if (!$model->service) return null;

        /**
         * Get file Instance
         */

        // Get the authorized Guzzle HTTP client
        $http = $model->client->authorize();

        // Download in 1 MB chunks
        $chunkSizeBytes = 1024 * 1024;
        $chunkStart = 0;


        $file = $model->service->files->get($id, [
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
}
