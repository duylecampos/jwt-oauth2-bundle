<?php

namespace JwtOAuth2Bundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerListener implements EventSubscriberInterface
{

    protected $reader;
    protected $container;

    public function __construct(Reader $reader, ContainerInterface $container)
    {
        /** @var Reader $reader */
        $this->reader = $reader;
        /** @var ContainerInterface $container */
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        $authorizationData = $this->getAuthorizationData($event->getRequest());
        $authorizedScopes = $this->getAuthorizedScopes($controller);
        if ($authorizedScopes) {
            $this->checkIfRequestScopeIsAuthorized($authorizationData['scopes'], $authorizedScopes);
        }

        $this->addAuthorizationDataInRequest($event->getRequest(), $authorizationData);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

    public function setConfig($repositoryName, $publicKey)
    {
        $this->repository = new $repositoryName();
        $this->publicKey = $publicKey;
    }

    private function getAuthorizedScopes($controller)
    {
        $scopes = null;
        $annotation = $this->getAnnotation($controller);
        if ($annotation) {
            $scopes = $annotation->getScopes();
        }
        return $scopes;
    }

    private function getAnnotation($controller)
    {
        $annotationName = 'JwtOAuth2Bundle\Annotation\Authenticated';
        list($controllerObject, $methodName) = $controller;

        $controllerReflectionObject = new \ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);
        $methodAnnotation = $this->reader->getMethodAnnotation($reflectionMethod, $annotationName);
        if ($methodAnnotation) {
            return $methodAnnotation;
        }

        $classAnnotation = $this->reader->getClassAnnotation(
            new \ReflectionClass(ClassUtils::getClass($controllerObject)),
            $annotationName
        );
        if ($classAnnotation) {
            return $classAnnotation;
        }

        return null;
    }

    private function getAuthorizationData(Request $request)
    {
        $repositoryName = $this->container->getParameter('jwt_o_auth2.access_token_repository.class');
        $accessTokenRepository = new $repositoryName();
        $publicKey = $this->container->getParameter('jwt_o_auth2.public_key.file');

        $server = new ResourceServer($accessTokenRepository, $publicKey);
        $psr7Request = (new DiactorosFactory())->createRequest($request);
        try {
            $psr7Request = $server->validateAuthenticatedRequest($psr7Request);
        } catch (OAuthServerException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }

        return [
            'scopes'      => $psr7Request->getAttribute('oauth_scopes'),
            'client_id'   => $psr7Request->getAttribute('oauth_client_id'),
            'user_id'     => $psr7Request->getAttribute('oauth_user_id')
        ];
    }

    private function checkIfRequestScopeIsAuthorized($requestScopes, $authorizedScopes)
    {
        foreach ($requestScopes as $requestScope) {
            if (in_array($requestScope, $authorizedScopes)) {
                return true;
            }
        }
        throw new AccessDeniedHttpException("Access Denied by scope.");
    }

    private function addAuthorizationDataInRequest(Request $request, $authorizationData)
    {
        $request->query->add([
            'oauth_scopes'      => $authorizationData['scopes'],
            'oauth_client_id'   => $authorizationData['client_id'],
            'oauth_user_id'     => $authorizationData['user_id']
        ]);
    }
}
