<?php 

namespace App\Service;

use League\OAuth2\Client\Provider\Github;
use Omines\OAuth2\Client\Provider\Gitlab;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Exception;

class AuthService
{
    private $provider;
    private $session;
    private $params;

    public function __construct(SessionInterface $session, ContainerBagInterface $params)
    {
        $this->session = $session;
        $this->params = $params;

        $oauthProvider = strtolower($params->get('app.oauthProvider'));
        switch($oauthProvider) {
            case 'github':
                $this->provider = new Github([
                    'clientId'          => $params->get('app.clientId'),
                    'clientSecret'      => $params->get('app.clientSecret'),
                    'redirectUri'       => $params->get('app.redirectUri'),
                    'domain'            => $params->get('app.domain')
                ]);
                break;
            case 'gitlab':
                $this->provider = new Gitlab([
                    'clientId'          => $params->get('app.clientId'),
                    'clientSecret'      => $params->get('app.clientSecret'),
                    'redirectUri'       => $params->get('app.redirectUri'),
                    'domain'            => $params->get('app.domain')
                ]);
                break;
            default:
                throw new Exception("Invalid Oauth Provider '{$oauthProvider}'!");
        }       
    }

    private function isCodeSet(Request $request) : bool 
    {
        return $request->query->get('code', null) !== null;
    }

    private function isSessionInvalid(Request $request) : bool 
    {
        return empty($request->query->get('state', null)) || ($request->query->get('state') !== $this->session->get('oauth2state', null));
    }

    private function isSessionValid(Request $request) : bool 
    {
        return !$this->isSessionInvalid($request);
    }

    public function processRequest(Request $request): Response
    {
        if (!$this->isCodeSet($request)) {
            // If we don't have an authorization code then get one
            $options = [
                'state' => bin2hex(random_bytes(64)),
                'scope' => $this->params->get('app.scopes') // array or string
            ];
            
            $authUrl = $this->provider->getAuthorizationUrl($options);
            $this->session->set('oauth2state', $this->provider->getState());
            return new RedirectResponse($authUrl);

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif ($this->isSessionInvalid($request)) {
            $this->session->remove('oauth2state');
            return new Response($content = 'Invalid state', $status = 500);
        }
    }

    public function getToken(Request $request): string
    {
        if ($this->isSessionValid($request)) {
            // Try to get an access token (using the authorization code grant)
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->query->get('code')
            ]);

            // Use this to interact with an API on the users behalf
            return $token->getToken();
        } else {
            throw new Exception("Invalid Session!");
        }
    }
}
