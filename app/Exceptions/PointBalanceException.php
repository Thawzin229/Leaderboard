<?php

namespace App\Exceptions;

use RuntimeException;

class PointBalanceException extends RuntimeException
{
    # this exception is thrown when a user tries to deduct points from their balance but they don't have enough points to do so.
    public function render()
    {
        return back()->with('error', $this->getMessage());
    }
}
