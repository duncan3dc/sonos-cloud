---
layout: default
title: Setup
permalink: /setup/
api: Interfaces.ApiInterface
---

All classes are in the `duncan3dc\Sonos\Cloud` namespace.

```php
require_once __DIR__ . "vendor/autoload.php";

use duncan3dc\Sonos\Cloud\Api;

$sonos = new Api(MY_API_KEY, MY_API_SECRET, AUTHENTICATION_REDIRECT_URL);
```


Authentication
--------------

The Sonos API uses OAuth to authenticate, you'll need a public facing webserver to complete authentication, below is an example script showing how to implement it:  

```php
$user = $sonos->getUser("your-sonos-username");

# If we haven't already authenticated this user
if (!$user->isAuthorised()) {

    # If this is the first request, send the user to Sonos to authenticate
    if (empty($_GET["code"])) {
        header("Location: " . $user->getUri());
        exit;
    }

    # Once the user is redirected back here, store their auth code in the cache
    $user->setCode($_GET["code"]);
}

# That's it, we've got an authenticated user now!
```
