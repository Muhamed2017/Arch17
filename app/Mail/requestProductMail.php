<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class requestProductMail extends Mailable
{
    use Queueable, SerializesModels;
    private $product_id, $product_name, $brand_name, $type, $message, $email, $phone, $product_image;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($product_id, $product_name, $brand_name, $type, $email, $phone, $message, $product_image)
    {
        $this->type = $type;
        $this->message = $message;
        $this->email = $email;
        $this->phone = $phone;
        $this->product_image = $product_image;
        $this->product_id = $product_id;
        $this->brand_name = $brand_name;
        $this->product_name = $product_name;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.requestProduct')->with([
            'type' => $this->type,
            'message' => $this->message,
            'email' => $this->email,
            'phone' => $this->phone,
            'product_image' => $this->product_image,
            'brand_name' => $this->brand_name,
            'product_name' => $this->product_name,
            'product_id' => $this->product_id,
        ]);
    }
}
