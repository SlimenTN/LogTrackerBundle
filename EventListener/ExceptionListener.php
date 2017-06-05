<?php
namespace SBC\LogTrackerBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        // You get the exception object from the received event
        $exception = $event->getException();

        $configuration = $this->container->getParameter('log_tracker');
        $sender = array($configuration['sender_mail'] => $configuration['app_name']);
        $recipients = $configuration['recipients'];

        $url = $event->getRequest()->getRequestUri();

        $subject = 'Exception has been thrown in "'.$configuration['app_name'].'" ';

        $message = 'URL: <strong>'.$url.'</strong><br><br>
                    Content: '.$exception->getMessage();
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($sender)
            ->setTo($recipients)
            ->setBody($message,
                'text/html'
            )
        ;
        
        $this->mailer->send($message);

//        var_dump($this->container->getParameter('log_tracker'));
//        die(var_dump($event));
//        $message = sprintf(
//            'My Error says: %s with code: %s',
//            $exception->getMessage(),
//            $exception->getCode()
//        );
//
//        // Customize your response object to display the exception details
//        $response = new Response();
//        $response->setContent($message);
//
//        // HttpExceptionInterface is a special type of exception that
//        // holds status code and header details
//        if ($exception instanceof HttpExceptionInterface) {
//
//            $response->setStatusCode($exception->getStatusCode());
//            $response->headers->replace($exception->getHeaders());
//        } else {
//
//            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//
//        // Send the modified response object to the event
//        $event->setResponse($response);
    }
}