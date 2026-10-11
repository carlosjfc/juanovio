@if($settings = \App\Support\Analytics::settings())
    <script type="application/json" id="analytics-settings">@json($settings)</script>
@endif
