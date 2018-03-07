<?php
namespace SBC\LogTrackerBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        
        $env = $this->container->getParameter('kernel.environment');
        if($env == 'prod' && !$exception instanceof NotFoundHttpException){//---if it's prod environment and not a NotFoundException catch it
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

            $this->mailer->send($message, $failures);

            $response = new Response();
            $response->setContent($this->view());

            $event->setResponse($response);
        }

    }

    /**
     * @param $handlerText
     * @return string
     */
    private function view(){
        return $this->container->get('twig')->render('@LogTracker/error_catcher.html.twig');
    }
}