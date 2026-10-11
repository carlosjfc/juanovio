@props(['text'])
@foreach(explode('privacy@juanovio.com', $text) as $part){{ $part }}@if(!$loop->last)<a href="mailto:privacy@juanovio.com">privacy@juanovio.com</a>@endif@endforeach
