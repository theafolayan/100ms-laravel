# 100ms Laravel SDK

The 100ms Laravel SDK provides an easy way to integrate the 100ms API with your Laravel application. It includes features for room management, generating room codes, and managing 100ms resources through secure management tokens.

## Features

- Generate and manage rooms.
- Generate room codes for prebuilt apps.
- Secure authentication with management tokens.
- Easy integration with Laravel through service providers and facades.

## Installation

### 1. Install the Package

Add the package to your Laravel project using Composer:

```bash
composer require theafolayan/100ms-laravel
```

### 2. Publish Configuration

Publish the configuration file to set your 100ms API credentials:

```bash
php artisan vendor:publish --provider="TheAfolayan\HmsLaravel\HmsServiceProvider"
```

This will create a `config/100ms.php` file.

## Configuration

Add your 100ms API credentials to your `.env` file:

```env
HMS_API_KEY=your_api_key
HMS_API_SECRET=your_api_secret
HMS_BASE_URL=https://api.100ms.live/v2
```

## Usage

### 1. Creating a Room

Use the Hms facade to create a new room:

```php
use TheAfolayan\HmsLaravel\Facades\Hms;

$response = Hms::createRoom([
    'name' => 'My Room',
    'template' => 'group_call',
]);

echo $response['id'];
```

### 2. Generating a Room Code

Generate a room code for a specific room:

```php
$roomId = 'room-id';
$response = Hms::generateRoomCode($roomId, [
    'role' => 'guest',
    'expiry' => time() + (60 * 30) // 30 minutes
]);

echo $response['code'];
```

### 3. Fetch Room Code Details

Retrieve details of a specific room code:

```php
$code = 'room-code';
$details = Hms::getRoomCodeDetails($code);

print_r($details);
```

### 4. List All Room Codes

List all room codes with optional parameters:

```php
$codes = Hms::listRoomCodes(['limit' => 10]);

print_r($codes);
```

### 5. Delete a Room Code

Delete a specific room code:

```php
$code = 'room-code-to-delete';
$response = Hms::deleteRoomCode($code);

echo $response['message'];
```

### 6. Enable or Disable a Room

Toggle a room's availability using the provided helper methods:

```php
$roomId = 'room-id';

// Disable the room
Hms::disableRoom($roomId);

// Enable the room
Hms::enableRoom($roomId);
```

## Testing

To test your package locally:

1. Set up a Laravel project and add your package as a local repository.
2. Use routes or controllers to test the package functionality.
3. Run unit tests for the package:

```bash
./vendor/bin/phpunit
```

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature-name`.
3. Commit your changes: `git commit -m "Add new feature"`.
4. Push to the branch: `git push origin feature-name`.
5. Open a pull request.

## License

This package is licensed under the [MIT License](https://opensource.org/license/mit).

## Support

If you encounter any issues or have feature requests, please open an issue in the GitHub repository.
