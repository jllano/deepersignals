<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use App\Model\User;

/**
 * This class is responsible for authenticating the user based on the "auth-token" header.
 */
class ApiTokenAuthenticator extends AbstractAuthenticator
{
    /**
     * This method is called when a request is made to the API with an "auth-token" header.
     * If the header is present, this method should return true to tell Symfony to call the authenticate() method.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('auth-token');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('auth-token');

        if (null == $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        // Use a callback to fetch the user based on the token
        $userBadge = new UserBadge($apiToken, function ($token) {
            // check the token in the env var.
            if ($token === $_ENV['API_TOKEN']) {
                return new User($token, [User::ROLE_USER]);
            }

            throw new CustomUserMessageAuthenticationException('Invalid token.');
        });

        return new SelfValidatingPassport($userBadge);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}