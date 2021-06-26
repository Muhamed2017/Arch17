<?php
namespace App\Support\Services;

use App\Models\Image;
use App\Models\User;
use App\Models\Product;
use App\Models\Company;
use App\Models\ProductDescription;
use App\Models\ProjectDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AddImagesToEntity2
{

    public $image_file ;
    public $entityClassName;
    public $folder;
    // public $uploadedImages;
    // public $entity;
    // public $entityClassName;
    // public $method;
    // public $folder;
    // public $options;
    // // public $tag;
    // public $thumbOptions = [
    //     "folder" => "Products/Thumbnails/",
    //     "overwrite" => TRUE,
    //     'transformation' => [
    //         "width" => 400,
    //         "crop" => "thumb"
    //     ]
    // ];

    // public $imgOptions = [
    //     "folder" => "Products/Images/",
    //     "overwrite" => TRUE,
    //     "crop" => "fill",
    //     // "crop" => "scale"
    // ];



    // public function __construct($entity)
    // {
    // //     if (!is_array($uploadedImages)) {
    // //         $uploadedImages = [$uploadedImages];
    // //     }

    // //     $this->uploadedImages = $uploadedImages;
    // //     $this->entity = $entity;
    // //     $this->options = $options;
    // //     $this->entityClassName = get_class($entity);
    //     // $this->tag = $tag;
    // }


    // public function execute()
    // {
    //     $images = collect();

    //     foreach ($this->uploadedImages as $image) {
    //         $images->push($this->uploadImage($image));
    //     }

    //     return $images;
    // }
    // public function uploadImage($image)
    // {

    //     if ($this->entityClassName === User::class) {

    //         return $this->uploadUserImage($image);
    //     }


    //     if ($this->entityClassName === Product::class) {

    //         return $this->uploadProductImage($image);
    //     }

    //     if ($this->entityClassName === ProductOptions::class) {

    //         return $this->uploadProductImage($image);
    //     }
    //     if ($this->entityClassName === ProductDescription::class) {

    //         return $this->uploadProductImage($image);
    //     }

    //     if ($this->entityClassName === Company::class) {

    //         return $this->uploadProductImage($image);
    //     }
    //     if ($this->entityClassName === ProjectDescription::class) {

    //         return $this->uploadProductImage($image);
    //     }
    //     return $this->uploadProductImage($image);
    // }


    // public function uploadToCloud($imagePath, $options)
    // {
    //     return Storage::disk('local')->put($imagePath, $options );

    // }


    // public function uploadUserImage($image)
    // {
    //     $options = array_merge($this->options, $this->imgOptions);

    //     $options['folder'] = $this->entity->imgFolderPath['image'];

    //     $cloudImage = $this->uploadToCloud($image->getRealPath(), $options);

    //     $thumbOptions = $this->thumbOptions;
    //     $thumbOptions['folder'] = $this->entity->imgFolderPath['thumb'];
    //     $thumbOptions['gravity'] = "faces";
    //     $cloudThumb = $this->uploadToCloud($cloudImage['url'], $thumbOptions);

    //     return $this->saveImage($cloudImage, $cloudThumb, $image->getClientOriginalName());
    // }

    
    // public function uploadProductImage($image)
    // {
    //     $options = array_merge($this->options, $this->imgOptions);

    //     //commented by muhamed gomaa
    //     //    $options['folder'] = $this->entity->imgFolderPath['image'];

    //     $cloudImage = $this->uploadToCloud($image->getRealPath(), $options);

    //     //commented by muhamed gomaa
    //     $thumbOptions = $this->thumbOptions;
    //     //        $thumbOptions['folder'] = $this->entity->imgFolderPath['thumb'];

    //     $cloudThumb = $this->uploadToCloud($cloudImage['url'], $this->thumbOptions);

    //     return $this->saveImage($cloudImage, $cloudThumb, $image->getClientOriginalName());
    // }


    // public function saveImage($image)
    // {

    //     $image = new Image;

    //     $image->img_url = $image;
    //     // $image->thumb_url = $cloudThumb['secure_url'];

    //     // $image->img_public_id = $cloudImage['public_id'];
    //     // $image->thumb_public_id = $cloudThumb['public_id'];
    //     // $image->img_width = $cloudImage['width'];
    //     // $image->img_height = $cloudImage['height'];
    //     // $image->thumb_width = $cloudThumb['width'];
    //     // $image->thumb_height = $cloudThumb['height'];
    //     // $image->img_bytes = $cloudImage['bytes'];
    //     // $image->thumb_bytes = $cloudThumb['bytes'];
    //     // $image->format = $cloudImage['format'];
    //     // $image->original_filename = $originalFileName;
    //     $this->entity->images()->save($image);

    //     return $image;
    // }



    public function UploadAndSave($entity , $image_file ) 
    {
        $image      = $image_file;
        $fileName   = time() . '.' . $image->getClientOriginalExtension();
        $path =Storage::disk('public')->put($fileName, $fileName );
        $url = Storage::Url('content_media/'. $fileName);
        $image = new Image;
        $image->img_url = 'content_media/'.$image;
        $image->thumb_url = 'content_media/'.$image;
        $image->imageable_id = $entity->project_id;
        $image->imageable_type = $entity;
        $image->save();
        return array($url,$image);
    } 
}