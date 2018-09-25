<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InternalException extends Exception
{
    protected $msgForUser;

    public function __construct(string $message, string $msgForUser, int $code = 400)
    {
      parent::__construct();

      $this->msgForUser = $msgForUser;
    }

    public function render()
    {
      if($request->expectsJson()) {
        return response()->json(['message' => $this->msgForUser], $code);
      }

      return view('pages.error', ['message' => $this->msgForUser]);
    }
}
