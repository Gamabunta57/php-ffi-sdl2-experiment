# PHP Game PoC

This project is just an experiment, a PoC about the feasability to run a game on a machine using PHP + SDL2 without running any server.

## Installation

You need to have PHP 7.4 minimum to be able to use PHP FFI.
Also, `composer` is our friend here. So, make sure it's installed and then just run:

```sh
composer install
```

There are PHP dependencies that don't come with the previous command. Please, take a look at the `./bin/php.ini` file and then, grab and install the library mentioned in it (and maybe adapt your PHP configuration to make these available).

### About the assets

To make this a "game" or a PoC for running a game under PHP, some assets are used but not shared in this repository (mainly to avoid copyright issues and things like that).
So, you might use your own. For that, the project requires the following files and folders structure:

- ./assets/music/music.wav
- ./assets/sprites/idle1.png
- ./assets/sprites/idle2.png
- ./assets/sprites/idle3.png
- ./assets/sprites/idle4.png

## Run the program

If your environment is set with PHP available from your terminal, simply do that:

```bash
php ./main.php
```

Or if you followed the structure I have locally:

```bash
./bin/php ./main.php
```

Or (under a windows instance):

```bash
./run.bat
```

Having a PHP executable in `./bin` folder will make use of the `php.ini` file given in the project. So if you had to grab the required PHP lib, you can put them in the `./bin` folder along with a PHP executable.

## What to expect running this program

Well, nothing really fancy, it's the shortest PoC I wanted to have to show (just for fun) that PHP could be used to run a game locally without any server.
So there should be a 4 frame animated character (if you took a character for the sprite assets) with a looping music in the background. On your keyboard you can use the arrow to move the sprite.

That's it! Nothing more (told you, it wasn't fancy).
