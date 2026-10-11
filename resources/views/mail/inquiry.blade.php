<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Commercial inquiry</title></head><body>
<h1>New commercial inquiry</h1>
@foreach($inquiry as $field => $value)
    @if($value !== '')<h2>{{ ucfirst($field) }}</h2><p style="white-space: pre-wrap">{{ $value }}</p>@endif
@endforeach
</body></html>
