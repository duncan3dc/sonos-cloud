---
layout: default
title: Players
permalink: /usage/players/
api: Interfaces.PlayerInterface
---

Get some information about a speaker:

```php
# Get the ID of the speaker
$player->getId();

# Get the room name assigned to this speaker
$player->getRoom();
```


Manage the volume of a speaker:

```php
if ($player->getVolume() > 50) {
    $player->setVolume(50);
}

$player->adjustVolume(10);
$player->adjustVolume(-10);
```


Mute a speaker:

```php
$player->mute();

if ($player->isMuted()) {
    $player->unmute();
}
```
