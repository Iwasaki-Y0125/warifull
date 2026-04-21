<?php

namespace Tests\Feature;

use Tests\TestCase;

class MiddlewareAuthTest extends TestCase
{
    // 認証が無効なときは、通常どおりアクセスできることを確認する。
    public function test_it_allows_access_when_middleware_auth_is_disabled(): void
    {
        config([
            'middleware_auth.enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // 認証が有効で資格情報が未送信なら、401で拒否されることを確認する。
    public function test_it_returns_401_when_auth_is_enabled_and_credentials_are_missing(): void
    {
        config([
            'middleware_auth.enabled' => true,
            'middleware_auth.username' => 'demo-user',
            'middleware_auth.password' => 'demo-pass',
            'middleware_auth.realm' => 'Warifull Demo',
        ]);

        $response = $this->get('/');

        $response->assertStatus(401);
        $response->assertHeader('WWW-Authenticate', 'Basic realm="Warifull Demo"');
    }

    // 認証が有効で資格情報が正しければ、アクセスを許可することを確認する。
    public function test_it_allows_access_with_valid_credentials(): void
    {
        config([
            'middleware_auth.enabled' => true,
            'middleware_auth.username' => 'demo-user',
            'middleware_auth.password' => 'demo-pass',
        ]);

        $response = $this
            ->withServerVariables([
                'PHP_AUTH_USER' => 'demo-user',
                'PHP_AUTH_PW' => 'demo-pass',
            ])
            ->get('/');

        $response->assertStatus(200);
    }

    // 認証が有効で資格情報が誤っていれば、401で拒否されることを確認する。
    public function test_it_returns_401_with_invalid_credentials(): void
    {
        config([
            'middleware_auth.enabled' => true,
            'middleware_auth.username' => 'demo-user',
            'middleware_auth.password' => 'demo-pass',
            'middleware_auth.realm' => 'Warifull Demo',
        ]);

        $response = $this
            ->withServerVariables([
                'PHP_AUTH_USER' => 'wrong-user',
                'PHP_AUTH_PW' => 'wrong-pass',
            ])
            ->get('/');

        $response->assertStatus(401);
        $response->assertHeader('WWW-Authenticate', 'Basic realm="Warifull Demo"');
    }

    // 認証を有効にしているのに設定値が空なら、503で停止することを確認する。
    public function test_it_returns_503_when_auth_is_enabled_but_credentials_are_not_configured(): void
    {
        config([
            'middleware_auth.enabled' => true,
            'middleware_auth.username' => '',
            'middleware_auth.password' => '',
        ]);

        $response = $this->get('/');

        $response->assertStatus(503);
    }
}
