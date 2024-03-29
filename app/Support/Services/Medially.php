<?php

// namespace CloudinaryLabs\CloudinaryLaravel;

namespace App\Support\Services;

use Exception;
use CloudinaryLabs\CloudinaryLaravel\Model\Media;

/**
 * Medially
 *
 * Provides functionality for attaching Cloudinary files to an eloquent model.
 * Whether the model should automatically reload its media relationship after modification.
 *
 */
trait Medially
{

    /**
     * Relationship for all attached media.
     */
    public function medially()
    {
        return $this->morphMany(Media::class, 'medially');
    }


    /**
     * Attach Media Files to a Model
     */
    public function attachMedia($file)
    {
        if (!file_exists($file)) {
            throw new Exception('Please pass in a file that exists');
        }

        $response = resolve(CloudinaryEngine::class)->uploadFile($file->getRealPath(), [
            'transformation' => [
                'width' => 50,
                'height' => 50
            ]
        ]);

        $media = new Media();
        $media->file_name = $response->getFileName();
        $media->file_url = $response->getSecurePath();
        $media->size = $response->getSize();
        $media->file_type = $response->getFileType();

        $this->medially()->save($media);
    }

    /**
     * Attach Rwmote Media Files to a Model
     */
    public function attachRemoteMedia($remoteFile)
    {
        $response = resolve(CloudinaryEngine::class)->uploadFile($remoteFile);

        $media = new Media();
        $media->file_name = $response->getFileName();
        $media->file_url = $response->getSecurePath();
        $media->size = $response->getSize();
        $media->file_type = $response->getFileType();

        $this->medially()->save($media);
    }

    /**
     * Get all the Media files relating to a particular Model record
     */
    public function fetchAllMedia()
    {
        return $this->medially()->get();
    }

    /**
     * Get the first Media file relating to a particular Model record
     */
    public function fetchFirstMedia()
    {
        return $this->medially()->first();
    }

    /**
     * Delete all/one file(s) associated with a particular Model record
     */
    public function detachMedia(Media $media = null)
    {

        $items = $this->medially()->get();

        foreach ($items as $item) {
            resolve(CloudinaryEngine::class)->destroy($item->getFileName());

            if (!is_null($media) && $item->id == $media->id) {
                return $item->delete();
            }
        }

        return $this->medially()->delete();
    }

    /**
     * Get the last Media file relating to a particular Model record
     */
    public function fetchLastMedia()
    {
        return $this->medially()->get()->last();
    }

    /**
     * Update the Media files relating to a particular Model record
     */
    public function updateMedia($file)
    {
        $this->detachMedia();
        $this->attachMedia($file);
    }

    /**
     * Update the Media files relating to a particular Model record (Specificially existing remote files)
     */
    public function updateRemoteMedia($file)
    {
        $this->detachMedia();
        $this->attachRemoteMedia($file);
    }
}
