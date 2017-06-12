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
            $handlerText = $configuration['handler_text'];

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
//        die(var_dump($exception));

            $response = new Response();
            $response->setContent($this->view($handlerText));

            $event->setResponse($response);
        }

    }

    private function view($handlerText){
        return '
            <html>
            <style>
                .error-header{
                    width: 50%;
                    margin: 0 auto;
                    padding: 30px;
                    background: #e09393;
                    color: #fff;
                    font-size: 40px;
                    font-family: Century gothic;
                }
                .error-body{
                    width: 50%;
                    margin: 0 auto;
                    padding: 30px;
                    background: #fff;
                    font-family: Century gothic;
                }
            </style>
            <body style="margin: 0;background: #eaeaea;">
            <div style="text-align: center; padding-top: 50px;">
                <div class="error-header">Something went wrong</div>
                <div class="error-body">
                    '.$handlerText.'<br>
                    <a href="javascript: window.history.back();">Go back to previous page</a>
                </div>
            </div>
            </body>
            </html>
        ';
    }
}