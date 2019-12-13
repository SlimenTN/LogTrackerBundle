<?php
namespace SBC\LogTrackerBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListener
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(\Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $env = $this->container->getParameter('kernel.environment');
        if($env == 'prod'){//---if it's prod environment catch it
            // You get the exception object from the received event
            $exception = $event->getException();
            $statusCode = $this->getStatusCode($exception);
            $configuration = $this->container->getParameter('log_tracker');
            $excludeExceptions = $configuration['exclude_exceptions'];

            if(! in_array($statusCode, $excludeExceptions)){
                $this->handleException($exception, $event, $configuration);
            }
        }
    }

    /**
     * @return string
     */
    private function view(){
        return $this->container->get('twig')->render('@LogTracker/error_catcher.html.twig');
    }

    /**
     * @param \Exception $exception
     * @return mixed
     */
    private function getStatusCode(\Exception $exception){
        $reflection = new \ReflectionObject($exception);
        // note : $reflection->getProperty('statusCode')
        // will not work because statusCode is a property
        // related to Symfony\Component\HttpKernel\Exception\HttpException
        $property = $reflection->getParentClass()->getProperty('statusCode');
        $property->setAccessible('public');
        return $property->getValue($exception);
    }

    /**
     * @param \Exception $exception
     * @param GetResponseForExceptionEvent $event
     * @param array $configuration
     */
    private function handleException(\Exception $exception, GetResponseForExceptionEvent $event, array $configuration){
        $sender = array($configuration['sender_mail'] => $configuration['app_name']);
        $recipients = $configuration['recipients'];

        $url = $event->getRequest()->getRequestUri();

        $subject = 'Exception has been thrown in "'.$configuration['app_name'].'" ';

        $content = $this->container->get('twig')->render('@LogTracker/mail_content.html.twig', array(
            'exception_url' => $url,
            'exception' => $exception,
        ));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($sender)
            ->setTo($recipients)
            ->setBody($content,
                'text/html'
            )
        ;

        $this->mailer->send($message, $failures);

        $response = null;
        if($configuration['response'] == 'twig'){
            $response = new Response();
            $response->setContent($this->view());
        }elseif($configuration['response'] == 'json'){
            $response = new JsonResponse(array(
                'success' => false,
                'message' => 'An unexpected error has occured and an error report has been sent to us to fix this as soon as possible',
                'exception' => $exception->getMessage(),
            ));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }

}