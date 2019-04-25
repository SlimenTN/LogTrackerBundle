<?php
namespace SBC\LogTrackerBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
                    'exception_trace' => $exception->getTrace(),
                ));
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }


            $event->setResponse($response);
        }

    }

    /**
     * @return string
     */
    private function view(){
        return $this->container->get('twig')->render('@LogTracker/error_catcher.html.twig');
    }
}