<?php

namespace App\Guards;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use App\Services\JwtService;

class JwtGuard implements Guard
{
    protected $provider;
    protected $request;
    protected $jwtService;
    protected $user;

    public function __construct(
        UserProvider $provider,
        Request $request,
        JwtService $jwtService
    ) {
        $this->provider = $provider;
        $this->request = $request;
        $this->jwtService = $jwtService;
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $token = $this->getTokenForRequest();

        if (!$token) {
            return null;
        }

        $payload = $this->jwtService->validateToken($token);

        if (isset($payload['error']) || !$this->jwtService->isTokenValid($token)) {
            return null;
        }

        $this->user = $this->provider->retrieveById($payload['sub']);
        return $this->user;
    }

    public function validate(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$user) {
            return false;
        }

        return $this->provider->validateCredentials($user, $credentials);
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function guest()
    {
        return !$this->check();
    }

    public function id()
    {
        if ($user = $this->user()) {
            return $user->getAuthIdentifier();
        }
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    protected function getTokenForRequest()
    {
        $token = $this->request->bearerToken();

        if (!$token && $this->request->has('access_token')) {
            $token = $this->request->input('access_token');
        }

        return $token;
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        // TODO: Implement hasUser() method.
    }
}