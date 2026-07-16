<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Exceptions;

use PhpRussianRequisites\Interfaces\ExceptionInterface;

class TypeCastingException extends \DomainException implements ExceptionInterface
{
}
