<?php
declare(strict_types=1);
namespace Coroq\Container\Exception;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

class InstantiationException extends LogicException implements ContainerExceptionInterface {
}
