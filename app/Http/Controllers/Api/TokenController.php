<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function revoke(Request $request)
    {
        $request->user()?->token()?->revoke();
        session()->forget(['pat', 'pat_scopes']);

        return response()->noContent();
    }
}
