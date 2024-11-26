<?php

namespace bld\ddosspelbord\classes\middleware;

use Closure;
use Laravel\Passport\Http\Middleware\CheckCredentials;
use Laravel\Passport\Exceptions\AuthenticationException;

class ReqAccesToken extends CheckCredentials {

    public function handle($request, Closure $next, ...$scopes)
    {
        try {
            // Let Passport natively verify token or it will block entry
            return parent::handle($request, $next, ...$scopes);
        }
        catch ( AuthenticationException $e ) {
            return response()->json([ 'error' => $e->getMessage() ], 500);
        }
    }

    /**
     * @desc mandetory, and a hook to add validation
     * @param $token
     * @return void
     */
    protected function validateCredentials( $token ) {
    }

    /**
     * @desc mandetory, and a hook to add validation scopes
     * @param $token
     * @param $scopes
     * @return void
     */
    protected function validateScopes( $token, $scopes ) {
    }
}



