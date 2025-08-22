<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    public function test_api_routes_are_registered(): void
    {
        $registeredRoutes = app('router')->getRoutes();
        $apiRoutes = [];
        
        foreach ($registeredRoutes as $route) {
            if (str_starts_with($route->uri(), 'api/')) {
                $apiRoutes[] = $route->uri();
            }
        }
        
        $expectedRoutes = [
            'api/auth/register',
            'api/auth/login',
            'api/auth/logout',
            'api/profiling-questions',
            'api/profile',
            'api/wallet',
            'api/points/claim',
        ];
        
        foreach ($expectedRoutes as $expectedRoute) {
            $this->assertContains($expectedRoute, $apiRoutes, "Route {$expectedRoute} should be registered");
        }
    }
    
    public function test_auth_register_endpoint_exists(): void
    {
        $response = $this->postJson('/api/auth/register', []);
        
        $response->assertStatus(422);
    }
    
    public function test_auth_login_endpoint_exists(): void
    {
        $response = $this->postJson('/api/auth/login', []);
        
        $response->assertStatus(422);
    }
    
    public function test_profiling_questions_endpoint_requires_auth(): void
    {
        $response = $this->getJson('/api/profiling-questions');
        
        $response->assertStatus(401);
    }
    
    public function test_wallet_endpoint_requires_auth(): void
    {
        $response = $this->getJson('/api/wallet');
        
        $response->assertStatus(401);
    }
}
