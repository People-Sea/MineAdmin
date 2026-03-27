<?php

declare(strict_types=1);

namespace App\Exception;

use Lcobucci\JWT\Exception;

class JwtInBlackException extends \Exception implements Exception {}
