<?php

declare(strict_types=1);

namespace Tests\CoRex\IoC\Helpers;

use CoRex\IoC\Exceptions\IoCException;

class BadClass
{
    /**
     * BadClass.
     *
     * @throws IoCException
     */
    public function __construct()
    {
        throw new IoCException('fail.on.purpose');
    }
}