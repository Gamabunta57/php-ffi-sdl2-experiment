<?php
require_once("vendor/autoload.php");

use Serafim\SDL\SDL;
use Serafim\SDL\Rect;
use Serafim\SDL\Event;
use Serafim\SDL\Kernel\Event\Type;
use Serafim\SDL\Image\Image;
use Serafim\SDL\AudioSpec;
use Serafim\SDL\RWopsPtr;

define("PLAYER_SPEED", 60.0);

function getTexture($sdl, $sdlImage, $renderer, $fileName) {
    
    $image = $sdlImage->loadTexture($renderer, $fileName);
    if (!$image) {
        printf("Can't load image (%s): %s", $fileName, $sdl->SDL_GetError());
        return;
    }
    return $image;
}

function processKeyEvent($keyEvent, &$player, $dt){
    $key = SDl::getInstance()->SDL_GetKeyName($keyEvent->keysym->sym);
    if ($key === "Up") {
        $player["y"] -= $dt * PLAYER_SPEED;
    } else if ($key === "Down") {
        $player["y"] += $dt * PLAYER_SPEED;
    }

    if ($key === "Left") {
        $player["x"] -= $dt * PLAYER_SPEED;
    } else if ($key === "Right") {
        $player["x"] += $dt * PLAYER_SPEED;
    }
}

$sdl = new SDL();
$sdl->SDL_Init(SDL::SDL_INIT_EVERYTHING);

$window = $sdl->SDL_CreateWindow( 
    'An SDL2 window',
    SDL::SDL_WINDOWPOS_UNDEFINED,
    SDL::SDL_WINDOWPOS_UNDEFINED, 
    640,
    480,
    SDL::SDL_WINDOW_OPENGL
);

if ($window === null) {
    throw new \Exception(sprintf('Could not create window: %s', $sdl->SDL_GetError()));
}
$sdlImage = new Image();
$sdlImage->init(Image::IMG_INIT_PNG);

$event = $sdl->new(Event::class);
$renderer = $sdl->SDL_CreateRenderer($window, -1, SDL::SDL_RENDERER_ACCELERATED | SDL::SDL_RENDERER_PRESENTVSYNC);
$sdl->SDL_SetRenderDrawColor($renderer, 0,0,0,0);

$lastTick = $sdl->SDL_GetTicks();

$rect = $sdl->new(Rect::class);
$player = [
    "x" => 0.0,
    "y" => 0.0,
];
$rect->x = $player["x"];
$rect->y = $player["y"];
$rect->w = 32;
$rect->h = 32;
$animationTimer = 10/60.0;
$currentTime = $animationTimer;
$frame = 0;
$textures = array();
for($i = 1; $i < 5; $i++){
    $fileName = __DIR__ ."/assets/sprites/idle{$i}.png";
    $textures[$i-1] = getTexture($sdl, $sdlImage, $renderer, $fileName);
    $sdl->SDL_SetTextureBlendMode($textures[$i-1], SDL::SDL_BLENDMODE_BLEND);
}

$wave_len = FFI::new("uint32_t");
$wave_buffer = FFI::new("uint8_t*");
$wave_spec = $sdl->new(AudioSpec::class);
$waveFileBytes = $sdl->SDL_RWFromFile("assets\\music\\music.wav", "rb");
$new_wave_spec = $sdl->SDL_LoadWAV_RW($waveFileBytes, 1, $sdl->addr($wave_spec), $sdl->addr($wave_buffer), $sdl->addr($wave_len));

if(is_null($new_wave_spec)) {
    throw new \Exception(sprintf('Could not create audio: %s', $sdl->SDL_GetError()));
}

$deviceId = $sdl->SDL_OpenAudioDevice(NULL, 0, $new_wave_spec, NULL, 0);
$sdl->SDL_PauseAudioDevice($deviceId, 0);
$sdl->SDL_QueueAudio($deviceId, $wave_buffer, $wave_len->cdata);

$running = true;
while ($running) {
    $sdl->SDL_PollEvent($sdl->addr($event));
    $currentTick = $sdl->SDL_GetTicks();
    $dt = ($sdl->SDL_GetTicks() - $lastTick)/ 1000.0;
    $lastTick = $currentTick;

    if ($event->type === Type::SDL_QUIT) {
        $running = false;
    } else if ($event->type === Type::SDL_KEYDOWN) {
        processKeyEvent($event->key, $player, $dt);
        $rect->x = $player["x"];
        $rect->y = $player["y"];
    }


    $currentTime -= $dt;
    
    if($currentTime <= 0.0) {
        $currentTime += $animationTimer;
        $frame++;
        if($frame >= 4) $frame = 0;
    }

    $queued = $sdl->SDL_GetQueuedAudioSize($deviceId);
    if ($queued <= $wave_len->cdata /2) {
        $sdl->SDL_DequeueAudio($deviceId, $wave_buffer, $wave_len->cdata);
        $sdl->SDL_QueueAudio($deviceId, $wave_buffer, $wave_len->cdata);
    }
    
    $sdl->SDL_RenderClear($renderer);
    $sdl->SDL_RenderCopy($renderer, $textures[$frame], NULL, $sdl->addr($rect));
    $sdl->SDL_RenderPresent($renderer);
}

for($i=0;$i < 4; $i++) {
    $sdl->SDL_DestroyTexture($textures[$i]);
}
$sdl->SDL_FreeWAV($wave_buffer);
$sdl->SDL_RWclose($waveFileBytes);
$sdl->SDL_CloseAudioDevice($deviceId);
$sdl->SDL_DestroyWindow($window);
$sdl->SDL_CloseAudio();
$sdl->SDL_FreeWAV($wave_buffer);
$sdl->SDL_Quit();