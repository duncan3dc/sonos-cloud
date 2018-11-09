---
layout: default
title: Getting Started
permalink: /usage/getting-started/
api: Interfaces.HouseholdInterface
---

Most actions start from the Household object which has a set of methods for getting Sonos resources.

## Household

Once you've got an authenticated [User object](../../setup/#authentication) you can get all the households they have like so:

```php
$households = $user->getHouseholds();
```

Or if you know the specific ID of the household you want to control:

```php
$household = $user->getHousehold("Sonos_G5Lv5sdgPfXZ5.hGpwSaya");
```


## Players

You can get all of the speakers available:

```php
$players = $household->getPlayers();
```

Or the players for a particular room:

```php
$players = $household->getPlayersByRoom("Living Room");
```

Or a single speaker for a particular room:

```php
$office = $household->getPlayerByRoom("Office");
```

[See what you can do with Players](../players/)
