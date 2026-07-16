<?php

declare(strict_types=1);

namespace PhpRussianRequisites\Exceptions;

use PhpRussianRequisites\Interfaces\ExceptionInterface;

class ValidationFailureException extends \DomainException implements ExceptionInterface
{
}
