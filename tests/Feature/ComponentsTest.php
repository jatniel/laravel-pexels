<?php

use Illuminate\Support\Facades\Http;
use Jatniel\Pexels\Tests\Helpers;

it('renders the image component with escaped attribution', function () {
    Http::fake([
        'api.pexels.com/v1/photos/12345' => Http::response(Helpers::photoData(['photographer' => '<script>x</script>'])),
    ]);

    $this->blade('<x-pexels-image id="12345" size="medium" attribution class="rounded" />')
        ->assertSee('src="https://images.pexels.com/photos/12345/medium.jpg"', false)
        ->assertSee('class="pexels-image rounded"', false)
        ->assertSee('&lt;script&gt;x&lt;/script&gt;', false)
        ->assertDontSee('<script>x</script>', false);
});

it('renders the background component with its slot', function () {
    Http::fake([
        'api.pexels.com/v1/photos/12345' => Http::response(Helpers::photoData()),
    ]);

    $this->blade('<x-pexels-background id="12345"><h1>Hello</h1></x-pexels-background>')
        ->assertSee("background-image: url('https://images.pexels.com/photos/12345/large2x.jpg')")
        ->assertSee('<h1>Hello</h1>', false);
});

it('renders nothing when the API fails', function () {
    Http::fake([
        'api.pexels.com/v1/*' => Http::response('Server Error', 500),
    ]);

    expect(trim((string) $this->blade('<x-pexels-image id="12345" />')))->toBe('');
});
