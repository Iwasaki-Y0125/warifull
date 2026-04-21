<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Basic認証を無効化している環境では、そのまま通過させる。
        if (! config('middleware_auth.enabled')) {
            return $next($request);
        }

        $username = (string) config('middleware_auth.username');
        $password = (string) config('middleware_auth.password');
        $realm = (string) config('middleware_auth.realm', 'Warifull Demo');

        // 認証を有効にしているのに資格情報が未設定なら、設定不備として503で停止する。
        if ($username === '' || $password === '') {
            return response('Service Unavailable', 503);
        }

        $providedUser = (string) $request->server('PHP_AUTH_USER', '');
        $providedPass = (string) $request->server('PHP_AUTH_PW', '');

        // 受け取った認証情報と設定値を比較する。
        $isAuthorized = hash_equals($username, $providedUser)
            && hash_equals($password, $providedPass);

        if ($isAuthorized) {
            return $next($request);
        }

        // 認証失敗時は401を返し、ブラウザにBasic認証ダイアログを表示させる。
        return response('Unauthorized', 401, [
            'WWW-Authenticate' => sprintf('Basic realm="%s"', addslashes($realm)),
        ]);
    }
}
