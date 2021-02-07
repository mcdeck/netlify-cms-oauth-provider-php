<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\AuthService;

class AuthController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(): Response
    {
        $oauthProvider = $this->getParameter('app.oauthProvider');
        return $this->render('index.html.twig', [
            'oauthProvider' => $oauthProvider
        ]);
    }

    /**
     * @Route("/auth")
     */
    public function auth(Request $request, AuthService $authService): Response
    {
        return $authService->processRequest($request);
    }

    /**
     * @Route("/callback")
     */
    public function callback(Request $request, AuthService $authService): Response
    {
        $oauthProvider = $this->getParameter('app.oauthProvider');
        $origin = $this->getParameter('app.origin');

        try {
            $content = [
                'token' => $authService->getToken($request),
                'provider' => $oauthProvider
            ]; 
            $message = 'success';
        } catch(Exception $e) {
            $content = $e;
            $message = "error";
        }   
        $content = json_encode($content);

        return $this->render('redirect.html.twig', [
            "content" => $content,
            "message" => $message,
            'originPattern' => $origin,
            'oauthProvider' => $oauthProvider
        ]);
    }

    /**
     * @Route("/success")
     */
    public function success(): Response
    {
        return new Response('', 204);
    }
}

?>