<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 */

namespace Amu\CasBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

/**
 * Processes a pre-authenticated authentication request.
 *
 * This authentication provider will not perform any checks on authentication
 * requests, as they should already be pre-authenticated. However, the
 * UserProviderInterface implementation may still throw a
 * UsernameNotFoundException, for example.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * Version personnalisé AMU MU du 23/12/2015 :
 * ===========================================
 *
 * @see Symfony/Component/Security/Core/Authentication/Provider/AuthenticationProviderInterface.php
 *
 */
class PreAuthenticatedAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $userChecker;
    private $providerKey;

    /**
     * Constructor.
     *
     * @param UserProviderInterface $userProvider An UserProviderInterface instance
     * @param UserCheckerInterface  $userChecker  An UserCheckerInterface instance
     * @param string                $providerKey  The provider key
     */
    public function __construct(UserProviderInterface $userProvider,  $userChecker, $providerKey)
    {
        // modif MU 23/12/2015 => 2ième paramètre $userChecker = non typé pour permettre string ""

        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }

        if (!$user = $token->getUser()) {
            throw new BadCredentialsException('No pre-authenticated principal found in request.');
        }

        $user = $this->userProvider->loadUserByUsername($user);

        // modif MU 23/12/2015
        if($this->userChecker!=""){
            $this->userChecker->checkPostAuth($user);
        }

        $authenticatedToken = new PreAuthenticatedToken($user, $token->getCredentials(), $this->providerKey, $user->getRoles());
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof PreAuthenticatedToken && $this->providerKey === $token->getProviderKey();
    }
}
