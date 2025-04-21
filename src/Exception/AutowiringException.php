<?php
declare(strict_types=1);
namespace Coroq\Container\Exception;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class AutowiringException extends InvalidArgumentException implements ContainerExceptionInterface {
}
