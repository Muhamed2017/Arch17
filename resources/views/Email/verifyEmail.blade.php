@component('mail::message')
# Introduction

Welcome Muhamed Gomaa,
here is Your Verification Code
## {{$code}}

This Code will expire in 30 Minuts

@component('mail::button', ['url' => ''])
Fuck You Man
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
