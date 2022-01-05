@component('mail::message')
# {{$type}} Mail

## {{$message}}
## <img src={{$product_image}} class="img-responsive">
## {{$product_name}}
### Brand : {{$brand_name}}

### User E-mail: {{$email}}
### User Phone:  {{$phone}}

@component('mail::button', ['url' => 'https://www.arch17test.live/product/'.$product_id])
Goto Product Page
@endcomponent

Thanks,<br>
{{-- {{ config('app.name') }} --}}
Arch17 Team
@endcomponent
